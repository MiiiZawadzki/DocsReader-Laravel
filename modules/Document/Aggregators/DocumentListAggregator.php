<?php

namespace Modules\Document\Aggregators;

use Illuminate\Support\Collection;
use Modules\Document\DTO\DocumentListFiltersDTO;
use Modules\Document\DTO\DocumentListItemDTO;
use Modules\Document\Enums\DocumentReadStatus;
use Modules\Document\Models\Document;
use Modules\Document\Services\DocumentService;
use Modules\History\Api\HistoryApiInterface;
use Modules\User\Api\UserApiInterface;

readonly class DocumentListAggregator
{
    public function __construct(
        private DocumentService $documentService,
        private HistoryApiInterface $historyApi,
        private UserApiInterface $userApi,
    ) {
    }

    /**
     * @param  int  $userId
     * @param  DocumentListFiltersDTO  $filters
     * @return Collection<int, DocumentListItemDTO>
     */
    public function getForUser(int $userId, DocumentListFiltersDTO $filters): Collection
    {
        $documents = $this->documentService->getForUser($userId, $filters->query);

        $readByDocumentId = $this->historyApi
            ->getReadStatusForDocuments($userId, $documents->pluck('id')->toArray())
            ->keyBy('documentId');

        if ($filters->status !== null) {
            $shouldBeRead = $filters->status === DocumentReadStatus::Read;
            $documents = $documents
                ->filter(fn(Document $document) => $readByDocumentId->has($document->getKey()) === $shouldBeRead)
                ->values();
        }

        $authorTags = $this->userApi->getUsersName(
            $documents->pluck('user_id')->unique()->toArray()
        );

        return $documents->map(fn(Document $document) => new DocumentListItemDTO(
            document: $document,
            readAt: $readByDocumentId->get($document->getKey())?->createdAt,
            authorTag: $authorTags->get($document->getAttribute('user_id')) ?? '-',
        ));
    }

    /**
     * Segment counts for the current search query, across all read statuses
     *
     * @param  int  $userId
     * @param  string|null  $query
     * @return array{all: int, read: int, unread: int}
     */
    public function countsForUser(int $userId, ?string $query): array
    {
        $documents = $this->documentService->getForUser($userId, $query);

        $readByDocumentId = $this->historyApi
            ->getReadStatusForDocuments($userId, $documents->pluck('id')->toArray())
            ->keyBy('documentId');

        $all = $documents->count();
        $read = $documents
            ->filter(fn(Document $document) => $readByDocumentId->has($document->getKey()))
            ->count();

        return ['all' => $all, 'read' => $read, 'unread' => $all - $read];
    }
}

<?php

namespace Modules\History\Aggregators;

use Illuminate\Support\Collection;
use Modules\Document\Api\DocumentApiInterface;
use Modules\Document\DTO\DocumentDTO;
use Modules\History\Api\HistoryApiInterface;
use Modules\History\DTO\HistoryListFiltersDTO;
use Modules\History\DTO\HistoryListItemDTO;
use Modules\History\Repositories\Contracts\DocumentReadRepositoryInterface;
use Modules\User\Api\UserApiInterface;

readonly class HistoryListAggregator
{
    public function __construct(
        private DocumentReadRepositoryInterface $documentReadRepository,
        private DocumentApiInterface $documentApi,
        private HistoryApiInterface $historyApi,
        private UserApiInterface $userApi,
    ) {
    }

    /**
     * @param  int  $userId
     * @param  HistoryListFiltersDTO  $filters
     * @return Collection<int, HistoryListItemDTO>
     */
    public function getForUser(int $userId, HistoryListFiltersDTO $filters): Collection
    {
        $readDocumentIds = $this->documentReadRepository
            ->getReadDocuments($userId)
            ->pluck('document_id')
            ->toArray();

        $documents = $this->documentApi->getDocumentsById($readDocumentIds, $filters->query);

        $readByDocumentId = $this->historyApi
            ->getReadStatusForDocuments($userId, $documents->pluck('id')->toArray())
            ->keyBy('documentId');

        $authorTags = $this->userApi->getUsersName(
            $documents->pluck('userId')->unique()->toArray()
        );

        return $documents->map(fn(DocumentDTO $document) => new HistoryListItemDTO(
            document: $document,
            readAt: $readByDocumentId->get($document->id)?->createdAt,
            authorTag: $authorTags->get($document->userId) ?? '-',
        ));
    }
}

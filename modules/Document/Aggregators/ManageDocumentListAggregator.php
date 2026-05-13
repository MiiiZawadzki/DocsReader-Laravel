<?php

namespace Modules\Document\Aggregators;

use Illuminate\Support\Collection;
use Modules\Document\DTO\DocumentListItemDTO;
use Modules\Document\Models\Document;
use Modules\Document\Services\DocumentService;
use Modules\User\Api\UserApiInterface;

readonly class ManageDocumentListAggregator
{
    public function __construct(
        private DocumentService $documentService,
        private UserApiInterface $userApi,
    ) {
    }

    /**
     * @param  int  $userId
     * @param  string|null  $query
     * @return Collection<int, DocumentListItemDTO>
     */
    public function getForManager(int $userId, ?string $query = null): Collection
    {
        $documents = $this->documentService->getForManager($userId, $query);

        $authorTags = $this->userApi->getUsersName(
            $documents->pluck('user_id')->unique()->toArray()
        );

        return $documents->map(fn(Document $document) => new DocumentListItemDTO(
            document: $document,
            readAt: null,
            authorTag: $authorTags->get($document->getAttribute('user_id')) ?? '-',
        ));
    }
}

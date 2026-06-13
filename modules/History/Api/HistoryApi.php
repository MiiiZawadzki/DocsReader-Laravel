<?php

namespace Modules\History\Api;

use Illuminate\Support\Collection;
use Modules\History\DTO\DocumentReadStatusDTO;
use Modules\History\Repositories\Contracts\DocumentReadRepositoryInterface;

readonly class HistoryApi implements HistoryApiInterface
{
    public function __construct(
        private DocumentReadRepositoryInterface $repository
    ) {}

    /**
     * @param  int  $userId
     * @param  array  $documentIds
     * @return Collection<int, DocumentReadStatusDTO>
     */
    public function getReadStatusForDocuments(int $userId, array $documentIds): Collection
    {
        return $this->repository->getReadStatusForDocuments($userId, $documentIds)
            ->map(fn ($documentRead) => DocumentReadStatusDTO::fromModel($documentRead));
    }

    /**
     * @param  int  $userId
     * @param  int  $documentId
     * @return DocumentReadStatusDTO
     */
    public function getReadStatusForDocument(int $userId, int $documentId): DocumentReadStatusDTO
    {
        $model = $this->repository->getReadStatusForDocument($userId, $documentId);

        return DocumentReadStatusDTO::fromModel($model);
    }

    /**
     * @param  int  $documentId
     * @return int
     */
    public function getDocumentReadCount(int $documentId): int
    {
        return $this->repository->getDocumentReadCount($documentId);
    }

    /**
     * @param  int  $userId
     * @return int
     */
    public function getUserDocumentReadCount(int $userId): int
    {
        return $this->repository->getUserDocumentReadCount($userId);
    }

    /**
     * @param  array  $documentsId
     * @return int
     */
    public function getDocumentsReadCount(array $documentsId): int
    {
        return $this->repository->getDocumentsReadCount($documentsId);
    }

    /**
     * @param  int  $documentId
     * @param  int|null  $skimThreshold
     * @return array
     */
    public function aggregateConfirmedStatsForDocument(int $documentId, ?int $skimThreshold = null): array
    {
        return $this->repository->aggregateConfirmedStatsForDocument($documentId, $skimThreshold);
    }

    /**
     * @param  int  $documentId
     * @param  array<int>  $userIds
     * @return array<int, array{confirmedAt: ?string, sessionId: ?int}>
     */
    public function confirmedReadsByUser(int $documentId, array $userIds): array
    {
        return $this->repository->confirmedReadsByUser($documentId, $userIds);
    }
}

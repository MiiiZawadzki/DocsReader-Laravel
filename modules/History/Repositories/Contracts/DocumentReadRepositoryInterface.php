<?php

namespace Modules\History\Repositories\Contracts;

use Illuminate\Support\Collection;
use Modules\History\Models\DocumentRead;

interface DocumentReadRepositoryInterface
{
    /**
     * @param  int  $documentId
     * @param  int  $userId
     * @param  int|null  $sessionId
     * @param  int|null  $totalActiveSeconds
     * @param  int|null  $pagesViewedCount
     * @return DocumentRead
     */
    public function markAsRead(
        int $documentId,
        int $userId,
        ?int $sessionId = null,
        ?int $totalActiveSeconds = null,
        ?int $pagesViewedCount = null,
    ): DocumentRead;

    /**
     * @param int $userId
     * @return Collection
     */
    public function getReadDocuments(int $userId): Collection;

    /**
     * @param int $userId
     * @param array $documentIds
     * @return Collection
     */
    public function getReadStatusForDocuments(int $userId, array $documentIds): Collection;

    /**
     * @param int $userId
     * @param int $documentId
     * @return DocumentRead|null
     */
    public function getReadStatusForDocument(int $userId, int $documentId): ?DocumentRead;

    /**
     * @param int $documentId
     * @return int
     */
    public function getDocumentReadCount(int $documentId): int;

    /**
     * @param int $userId
     * @return int
     */
    public function getUserDocumentReadCount(int $userId): int;

    /**
     * @param array $documentsId
     * @return int
     */
    public function getDocumentsReadCount(array $documentsId): int;

    /**
     * @param  int  $documentId
     * @param  int|null  $skimThreshold
     * @return array
     */
    public function aggregateConfirmedStatsForDocument(int $documentId, ?int $skimThreshold = null): array;

    /**
     * For each confirmed user on this document, return their confirmation
     * timestamp and the session id that did the confirming.
     *
     * @param  int  $documentId
     * @param  array<int>  $userIds
     * @return array<int, array{confirmedAt: ?string, sessionId: ?int}>
     */
    public function confirmedReadsByUser(int $documentId, array $userIds): array;
}

<?php

namespace Modules\History\Api;

use Illuminate\Support\Collection;
use Modules\History\DTO\DocumentReadStatusDTO;

interface HistoryApiInterface
{
    /**
     * @param  int  $userId
     * @param  array  $documentIds
     * @return Collection<int, DocumentReadStatusDTO>
     */
    public function getReadStatusForDocuments(int $userId, array $documentIds): Collection;

    /**
     * @param  int  $userId
     * @param  int  $documentId
     * @return DocumentReadStatusDTO
     */
    public function getReadStatusForDocument(int $userId, int $documentId): DocumentReadStatusDTO;

    /**
     * @param  int  $documentId
     * @return int
     */
    public function getDocumentReadCount(int $documentId): int;

    /**
     * @param  int  $userId
     * @return int
     */
    public function getUserDocumentReadCount(int $userId): int;

    /**
     * @param  array  $documentsId
     * @return int
     */
    public function getDocumentsReadCount(array $documentsId): int;

    /**
     * @param  int  $documentId
     * @param  int|null  $skimThreshold
     * @return array{confirmedCount: int, avgPagesViewed: int, avgActiveSeconds: int, skimCount: int}
     */
    public function aggregateConfirmedStatsForDocument(int $documentId, ?int $skimThreshold = null): array;

    /**
     * @param  int  $documentId
     * @param  array<int>  $userIds
     * @return array<int, array{confirmedAt: ?string, sessionId: ?int}>
     */
    public function confirmedReadsByUser(int $documentId, array $userIds): array;
}

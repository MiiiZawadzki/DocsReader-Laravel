<?php

namespace Modules\Engagement\Api;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EngagementApiInterface
{
    /**
     * @param  int  $userId
     * @param  string  $documentUuid
     * @return array{allowed: bool, missingPages: int[], minSecondsPerPage: int, totalPages: ?int}
     */
    public function evaluate(int $userId, string $documentUuid): array;

    /**
     * @param  int  $userId
     * @param  int  $documentId
     * @return array{totalActiveSeconds: int, pagesViewedCount: int}
     */
    public function snapshot(int $userId, int $documentId): array;

    /**
     * @param  string  $sessionUuid
     * @param  int  $userId
     * @return int|null
     */
    public function findSessionIdByUuid(string $sessionUuid, int $userId): ?int;

    /**
     * @param  int  $documentId
     * @return array{count: int, avgSeconds: int}
     */
    public function sessionStatsForDocument(int $documentId): array;

    /**
     * @param  int  $documentId
     * @return array<int, array{pageNumber: int, avgSeconds: int, viewerCount: int}>
     */
    public function pageHeatmapForDocument(int $documentId): array;

    /**
     *  Paginator items expose: sessionUuid, userId, startedAt, lastTickAt, endedAt, totalActiveSeconds, lastPage.
     *
     * @param  int  $documentId
     * @param  int  $page
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function sessionsPageForDocument(int $documentId, int $page, int $perPage): LengthAwarePaginator;

    /**
     * @param  array<int>  $sessionIds
     * @return array<int, string> id → uuid
     */
    public function sessionUuidsByIds(array $sessionIds): array;

    /**
     * Furthest page reached by each session, keyed by session uuid.
     *
     * @param  array<string>  $sessionUuids
     * @return array<string, int>
     */
    public function furthestPageBySessionUuids(array $sessionUuids): array;
}

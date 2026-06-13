<?php

namespace Modules\Engagement\Api;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Engagement\Repositories\Contracts\PageProgressRepositoryInterface;
use Modules\Engagement\Repositories\Contracts\PageTickRepositoryInterface;
use Modules\Engagement\Repositories\Contracts\ReadingSessionRepositoryInterface;
use Modules\Engagement\Services\EngagementGate;

readonly class EngagementApi implements EngagementApiInterface
{
    public function __construct(
        private EngagementGate $gate,
        private ReadingSessionRepositoryInterface $sessions,
        private PageProgressRepositoryInterface $progress,
        private PageTickRepositoryInterface $ticks,
    ) {}

    /**
     * @param  int  $userId
     * @param  string  $documentUuid
     * @return array
     */
    public function evaluate(int $userId, string $documentUuid): array
    {
        return $this->gate->evaluate($userId, $documentUuid);
    }

    /**
     * @param  int  $userId
     * @param  int  $documentId
     * @return array
     */
    public function snapshot(int $userId, int $documentId): array
    {
        return $this->gate->snapshot($userId, $documentId);
    }

    /**
     * @param  string  $sessionUuid
     * @param  int  $userId
     * @return int|null
     */
    public function findSessionIdByUuid(string $sessionUuid, int $userId): ?int
    {
        $session = $this->sessions->findByUuid($sessionUuid);
        if ($session === null || $session->user_id !== $userId) {
            return null;
        }

        return $session->id;
    }

    /**
     * @param  int  $documentId
     * @return array
     */
    public function sessionStatsForDocument(int $documentId): array
    {
        return $this->sessions->statsForDocument($documentId);
    }

    /**
     * @param  int  $documentId
     * @return array|array[]
     */
    public function pageHeatmapForDocument(int $documentId): array
    {
        return $this->progress->heatmapForDocument($documentId);
    }

    /**
     * @param  int  $documentId
     * @param  int  $page
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function sessionsPageForDocument(int $documentId, int $page, int $perPage): LengthAwarePaginator
    {
        return $this->sessions->paginateForDocument($documentId, $page, $perPage)
            ->through(fn ($session) => [
                'sessionUuid' => $session->uuid,
                'userId' => (int) $session->user_id,
                'startedAt' => optional($session->started_at)->toIso8601String(),
                'lastTickAt' => optional($session->last_tick_at)->toIso8601String(),
                'endedAt' => optional($session->ended_at)->toIso8601String(),
                'totalActiveSeconds' => (int) $session->total_active_seconds,
                'lastPage' => (int) $session->last_page,
            ]);
    }

    /**
     * @param  array<int>  $sessionIds
     * @return array<int, string> id → uuid
     */
    public function sessionUuidsByIds(array $sessionIds): array
    {
        return $this->sessions->uuidsByIds($sessionIds);
    }

    /**
     * @param  array<string>  $sessionUuids
     * @return array<string, int>
     */
    public function furthestPageBySessionUuids(array $sessionUuids): array
    {
        return $this->ticks->maxPageBySessionUuids($sessionUuids);
    }
}

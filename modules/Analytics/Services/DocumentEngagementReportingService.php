<?php

namespace Modules\Analytics\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Analytics\Strategies\Contracts\CompletionRateStrategyInterface;
use Modules\Analytics\Strategies\Contracts\SkimDetectionStrategyInterface;
use Modules\Document\Api\DocumentApiInterface;
use Modules\Engagement\Api\EngagementApiInterface;
use Modules\History\Api\HistoryApiInterface;
use Modules\User\Api\UserApiInterface;

/**
 * Composes the engagement dashboard view from Engagement + History + Document + User APIs.
 */
readonly class DocumentEngagementReportingService
{
    public function __construct(
        private DocumentApiInterface $documentApi,
        private EngagementApiInterface $engagementApi,
        private HistoryApiInterface $historyApi,
        private UserApiInterface $userApi,
        private CompletionRateStrategyInterface $completionRate,
        private SkimDetectionStrategyInterface $skimDetection,
    ) {
    }

    /**
     * @param  int  $documentId
     * @return array{
     *   totalSessions: int,
     *   avgTotalSeconds: int,
     *   avgPagesViewed: int,
     *   completionRate: float,
     *   skimRate: float,
     *   minSecondsPerPage: int,
     *   totalPages: ?int
     * }
     */
    public function summary(int $documentId): array
    {
        $document = $this->documentApi->getDocumentsById([$documentId])->first();
        $totalPages = $document?->totalPages;
        $minSeconds = $document?->delay ?? 0;

        $sessionStats = $this->engagementApi->sessionStatsForDocument($documentId);
        $skimThreshold = $this->skimDetection->thresholdFor($totalPages, $minSeconds);
        $readStats = $this->historyApi->aggregateConfirmedStatsForDocument($documentId, $skimThreshold);
        $assignedCount = $this->documentApi->getAssignedUsersCount($documentId);

        return [
            'totalSessions' => $sessionStats['count'],
            'avgTotalSeconds' => $sessionStats['avgSeconds'],
            'avgPagesViewed' => $readStats['avgPagesViewed'],
            'completionRate' => $this->completionRate->calculate($readStats['confirmedCount'], $assignedCount),
            'skimRate' => $this->skimDetection->rate($readStats['skimCount'], $readStats['confirmedCount']),
            'minSecondsPerPage' => $minSeconds,
            'totalPages' => $totalPages,
        ];
    }

    /**
     * @param  int  $documentId
     * @return array<int, array{pageNumber: int, avgSeconds: int, viewerCount: int}>
     */
    public function pageHeatmap(int $documentId): array
    {
        return $this->engagementApi->pageHeatmapForDocument($documentId);
    }

    /**
     * @param  int  $documentId
     * @param  int  $page
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function sessions(int $documentId, int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        $paginator = $this->engagementApi->sessionsPageForDocument($documentId, $page, $perPage);

        $userIds = array_values(
            array_unique(
                array_map(
                    fn($item) => (int)$item['userId'],
                    $paginator->items(),
                )
            )
        );

        $userNames = $userIds === []
            ? collect()
            : $this->userApi->getUsersName($userIds);

        // Per-user confirmation info: {userId => {confirmedAt, sessionId}}
        $confirmedReads = $this->historyApi->confirmedReadsByUser($documentId, $userIds);

        $confirmingIds = array_values(
            array_filter(
                array_map(
                    fn($r) => $r['sessionId'] ?? null,
                    $confirmedReads,
                )
            )
        );
        $idToUuid = $confirmingIds === []
            ? []
            : $this->engagementApi->sessionUuidsByIds($confirmingIds);

        $confirmingUuidSet = array_flip(array_values($idToUuid));

        // Furthest page reached per session
        $pageSessionUuids = array_map(fn($item) => $item['sessionUuid'], $paginator->items());
        $furthestByUuid = $pageSessionUuids === []
            ? []
            : $this->engagementApi->furthestPageBySessionUuids($pageSessionUuids);

        return $paginator->through(
            function ($item) use ($userNames, $confirmedReads, $confirmingUuidSet, $furthestByUuid) {
                $userId = (int)$item['userId'];
                $isConfirmingSession = isset($confirmingUuidSet[$item['sessionUuid']]);
                $userConfirmation = $confirmedReads[$userId] ?? null;

                return [
                    'sessionUuid' => $item['sessionUuid'],
                    'userId' => $userId,
                    'userName' => $userNames->get($userId),
                    'startedAt' => $item['startedAt'],
                    'lastTickAt' => $item['lastTickAt'],
                    'endedAt' => $item['endedAt'],
                    'totalActiveSeconds' => $item['totalActiveSeconds'],
                    'lastPage' => $item['lastPage'],
                    'furthestPage' => $furthestByUuid[$item['sessionUuid']] ?? $item['lastPage'],
                    'confirmedAt' => $isConfirmingSession ? ($userConfirmation['confirmedAt'] ?? null) : null,
                    'userHasConfirmed' => $userConfirmation !== null,
                ];
            }
        );
    }
}

<?php

namespace Modules\Engagement\Services;

use Modules\Document\Api\DocumentApiInterface;
use Modules\Engagement\Repositories\Contracts\PageProgressRepositoryInterface;
use Modules\Engagement\Repositories\Contracts\ReadingSessionRepositoryInterface;
use Modules\Engagement\Strategies\Contracts\EngagementRuleStrategyInterface;

readonly class EngagementGate
{
    public const REASON_NOT_FOUND = 'not_found';

    public const REASON_NO_SESSION = 'no_session';

    public const REASON_PAGE_COUNT_UNKNOWN = 'page_count_unknown';

    public const REASON_INCOMPLETE = 'incomplete';

    public function __construct(
        private PageProgressRepositoryInterface $progress,
        private ReadingSessionRepositoryInterface $sessions,
        private DocumentApiInterface $documentApi,
        private EngagementRuleStrategyInterface $rule,
    ) {
    }

    /**
     * @param  int  $userId
     * @param  string  $documentUuid
     * @return array{allowed: bool, reason: ?string, missingPages: int[], minSecondsPerPage: int, totalPages: ?int}
     */
    public function evaluate(int $userId, string $documentUuid): array
    {
        $document = $this->documentApi->getDocumentByUuid($documentUuid);
        if ($document === null) {
            return $this->result(false, self::REASON_NOT_FOUND, [], 0, null);
        }

        // No per-page minimum was set, so there is no reading time to enforce
        if ($document->delay <= 0) {
            return $this->result(true, null, [], $document->delay, $document->totalPages);
        }

        $knowsPageCount = $document->totalPages !== null && $document->totalPages >= 1;

        // Reading time is required but nothing was ever recorded, so there is no evidence the document was opened
        if (!$this->sessions->hasAnyForUserDocument($userId, $document->id)) {
            return $this->result(
                false,
                self::REASON_NO_SESSION,
                $knowsPageCount ? range(1, $document->totalPages) : [],
                $document->delay,
                $document->totalPages,
            );
        }

        // A per-page minimum can't be verified without knowing how many pages there are
        if (!$knowsPageCount) {
            return $this->result(false, self::REASON_PAGE_COUNT_UNKNOWN, [], $document->delay, $document->totalPages);
        }

        $secondsByPage = [];
        foreach ($this->progress->forUserDocument($userId, $document->id) as $row) {
            $secondsByPage[(int)$row->page_number] = (int)$row->total_active_seconds;
        }

        $missing = $this->rule->missingPages($secondsByPage, $document->totalPages, $document->delay);

        return $this->result(
            empty($missing),
            empty($missing) ? null : self::REASON_INCOMPLETE,
            $missing,
            $document->delay,
            $document->totalPages,
        );
    }

    /**
     * Snapshot used by History when finalizing a confirmed read.
     *
     * @param  int  $userId
     * @param  int  $documentId
     * @return array{totalActiveSeconds: int, pagesViewedCount: int}
     */
    public function snapshot(int $userId, int $documentId): array
    {
        $rows = $this->progress->forUserDocument($userId, $documentId);
        $total = 0;
        $pages = 0;
        foreach ($rows as $row) {
            if ((int)$row->total_active_seconds > 0) {
                $total += (int)$row->total_active_seconds;
                $pages++;
            }
        }

        return ['totalActiveSeconds' => $total, 'pagesViewedCount' => $pages];
    }

    /**
     * @param  bool  $allowed
     * @param  string|null  $reason
     * @param  int[]  $missingPages
     * @param  int  $minSecondsPerPage
     * @param  int|null  $totalPages
     * @return array{allowed: bool, reason: ?string, missingPages: int[], minSecondsPerPage: int, totalPages: ?int}
     */
    private function result(
        bool $allowed,
        ?string $reason,
        array $missingPages,
        int $minSecondsPerPage,
        ?int $totalPages,
    ): array {
        return [
            'allowed' => $allowed,
            'reason' => $reason,
            'missingPages' => $missingPages,
            'minSecondsPerPage' => $minSecondsPerPage,
            'totalPages' => $totalPages,
        ];
    }
}

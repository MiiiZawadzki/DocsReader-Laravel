<?php

namespace Modules\Engagement\Services;

use Modules\Document\Api\DocumentApiInterface;
use Modules\Engagement\Repositories\Contracts\PageProgressRepositoryInterface;
use Modules\Engagement\Repositories\Contracts\ReadingSessionRepositoryInterface;
use Modules\Engagement\Strategies\Contracts\EngagementRuleStrategyInterface;

readonly class EngagementGate
{
    public function __construct(
        private PageProgressRepositoryInterface $progress,
        private ReadingSessionRepositoryInterface $sessions,
        private DocumentApiInterface $documentApi,
        private EngagementRuleStrategyInterface $rule,
    ) {}

    /**
     * @param  int  $userId
     * @param  string  $documentUuid
     * @return array{allowed: bool, missingPages: int[], minSecondsPerPage: int, totalPages: ?int}
     */
    public function evaluate(int $userId, string $documentUuid): array
    {
        $document = $this->documentApi->getDocumentByUuid($documentUuid);
        if ($document === null) {
            return ['allowed' => false, 'missingPages' => [], 'minSecondsPerPage' => 0, 'totalPages' => null];
        }

        // Backward-compat: a user who never started a session is allowed through unchanged
        if (! $this->sessions->hasAnyForUserDocument($userId, $document->id)) {
            return [
                'allowed' => true,
                'missingPages' => [],
                'minSecondsPerPage' => $document->delay,
                'totalPages' => $document->totalPages,
            ];
        }

        // Can't enforce a per-page minimum we don't know
        if ($document->totalPages === null || $document->totalPages < 1 || $document->delay <= 0) {
            return [
                'allowed' => true,
                'missingPages' => [],
                'minSecondsPerPage' => $document->delay,
                'totalPages' => $document->totalPages,
            ];
        }

        $secondsByPage = [];
        foreach ($this->progress->forUserDocument($userId, $document->id) as $row) {
            $secondsByPage[(int) $row->page_number] = (int) $row->total_active_seconds;
        }

        $missing = $this->rule->missingPages($secondsByPage, $document->totalPages, $document->delay);

        return [
            'allowed' => empty($missing),
            'missingPages' => $missing,
            'minSecondsPerPage' => $document->delay,
            'totalPages' => $document->totalPages,
        ];
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
            if ((int) $row->total_active_seconds > 0) {
                $total += (int) $row->total_active_seconds;
                $pages++;
            }
        }

        return ['totalActiveSeconds' => $total, 'pagesViewedCount' => $pages];
    }
}

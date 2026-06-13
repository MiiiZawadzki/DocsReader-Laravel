<?php

namespace Modules\Engagement\Strategies;

use Modules\Engagement\Strategies\Contracts\EngagementRuleStrategyInterface;

/**
 * Every page from 1..totalPages must have been viewed for at least minSecondsPerPage.
 */
class EveryPageMeetsThresholdRule implements EngagementRuleStrategyInterface
{
    /**
     * @param  array  $secondsByPage
     * @param  int  $totalPages
     * @param  int  $minSecondsPerPage
     * @return array
     */
    public function missingPages(array $secondsByPage, int $totalPages, int $minSecondsPerPage): array
    {
        $missing = [];
        for ($p = 1; $p <= $totalPages; $p++) {
            $seconds = (int) ($secondsByPage[$p] ?? 0);
            if ($seconds < $minSecondsPerPage) {
                $missing[] = $p;
            }
        }

        return $missing;
    }
}

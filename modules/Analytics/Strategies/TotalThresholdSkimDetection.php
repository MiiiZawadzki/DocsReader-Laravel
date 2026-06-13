<?php

namespace Modules\Analytics\Strategies;

use Modules\Analytics\Strategies\Contracts\SkimDetectionStrategyInterface;

/**
 * A read is "skimmed" when total_active_seconds is below
 * totalPages * minSecondsPerPage — i.e. the user couldn't have spent the
 * required minimum across all pages.
 */
class TotalThresholdSkimDetection implements SkimDetectionStrategyInterface
{
    /**
     * @param  int|null  $totalPages
     * @param  int  $minSecondsPerPage
     * @return int|null
     */
    public function thresholdFor(?int $totalPages, int $minSecondsPerPage): ?int
    {
        if ($totalPages === null || $totalPages < 1 || $minSecondsPerPage <= 0) {
            return null;
        }

        return $totalPages * $minSecondsPerPage;
    }

    /**
     * @param  int  $skimCount
     * @param  int  $confirmedCount
     * @return float
     */
    public function rate(int $skimCount, int $confirmedCount): float
    {
        if ($confirmedCount <= 0) {
            return 0.0;
        }

        return round($skimCount / $confirmedCount, 4);
    }
}

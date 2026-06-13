<?php

namespace Modules\Analytics\Strategies;

use Modules\Analytics\Strategies\Contracts\CompletionRateStrategyInterface;

/**
 * Confirmed reads / assigned users.
 */
class ProportionalCompletionRate implements CompletionRateStrategyInterface
{
    /**
     * @param  int  $confirmedCount
     * @param  int  $assignedCount
     * @return float
     */
    public function calculate(int $confirmedCount, int $assignedCount): float
    {
        if ($assignedCount <= 0) {
            return 0.0;
        }

        return round($confirmedCount / $assignedCount, 4);
    }
}

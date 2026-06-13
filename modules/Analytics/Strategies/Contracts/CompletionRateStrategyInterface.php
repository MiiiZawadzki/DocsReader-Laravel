<?php

namespace Modules\Analytics\Strategies\Contracts;

interface CompletionRateStrategyInterface
{
    /**
     * Compute the completion rate for a document.
     *
     * @param  int  $confirmedCount
     * @param  int  $assignedCount
     * @return float
     */
    public function calculate(int $confirmedCount, int $assignedCount): float;
}

<?php

namespace Modules\Analytics\Strategies\Contracts;

interface SkimDetectionStrategyInterface
{
    /**
     *  Total active seconds below which a confirmed read counts as "skimmed".
     *  Returning null disables skim detection for this document (e.g. when the
     *  inputs are missing) — the History query then skips its skim count.
     *
     * @param  int|null  $totalPages
     * @param  int  $minSecondsPerPage
     * @return int|null
     */
    public function thresholdFor(?int $totalPages, int $minSecondsPerPage): ?int;

    /**
     *  The skim rate = skim count / confirmed count.
     *
     * @param  int  $skimCount
     * @param  int  $confirmedCount
     * @return float
     */
    public function rate(int $skimCount, int $confirmedCount): float;
}

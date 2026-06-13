<?php

namespace Modules\Engagement\Strategies\Contracts;

interface EngagementRuleStrategyInterface
{
    /**
     *  Given a user's per-page progress and the document's thresholds, return
     *  the page numbers that don't yet satisfy the rule. An empty list means
     *  the user is allowed to confirm.
     *
     * @param  array  $secondsByPage
     * @param  int  $totalPages
     * @param  int  $minSecondsPerPage
     * @return array
     */
    public function missingPages(array $secondsByPage, int $totalPages, int $minSecondsPerPage): array;
}

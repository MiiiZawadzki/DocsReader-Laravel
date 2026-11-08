<?php

namespace Modules\Engagement\Repositories\Contracts;

interface ReadingBudgetRepositoryInterface
{
    /**
     * Read the user's reading clock with a row lock, creating it on first use.
     *
     * @param  int  $userId
     * @return int Epoch milliseconds up to which reading time has been credited, 0 if never.
     */
    public function creditedUntilMsForUpdate(int $userId): int;

    /**
     * @param  int  $userId
     * @param  int  $creditedUntilMs
     * @return void
     */
    public function setCreditedUntilMs(int $userId, int $creditedUntilMs): void;
}

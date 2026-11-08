<?php

namespace Modules\Engagement\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Engagement\Repositories\Contracts\ReadingBudgetRepositoryInterface;

class ReadingBudgetRepository implements ReadingBudgetRepositoryInterface
{
    /**
     * @param  int  $userId
     * @return int
     */
    public function creditedUntilMsForUpdate(int $userId): int
    {
        DB::table('reading_time_budgets')->insertOrIgnore([
            'user_id' => $userId,
            'credited_until_ms' => 0,
        ]);

        return (int) DB::table('reading_time_budgets')
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->value('credited_until_ms');
    }

    /**
     * @param  int  $userId
     * @param  int  $creditedUntilMs
     * @return void
     */
    public function setCreditedUntilMs(int $userId, int $creditedUntilMs): void
    {
        DB::table('reading_time_budgets')
            ->where('user_id', $userId)
            ->update(['credited_until_ms' => $creditedUntilMs]);
    }
}

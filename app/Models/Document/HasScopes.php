<?php

namespace App\Models\Document;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait HasScopes
{
    /**
     * @param Builder $query
     * @param User $user
     * @return void
     */
    function scopeForUser(Builder $query, User $user): void
    {
        $query->whereHas('userDocuments', function (Builder $builder) use ($user) {
            $builder->where('user_id', $user->getKey());
        });
    }

    /**
     * @param Builder $query
     * @param User $user
     * @return void
     */
    function scopeForManager(Builder $query, User $user): void
    {
        $query->where('user_id', $user->getKey());
    }

    /**
     * @param Builder $query
     * @param Carbon $date
     * @return void
     */
    function scopeForDate(Builder $query, Carbon $date): void
    {
        $query->whereDate('date_from', '<=', $date)
            ->where(function (Builder $query) use ($date) {
                $query->whereNull('date_to')
                    ->orWhereDate('date_to', '>=', $date);
            });
    }
}

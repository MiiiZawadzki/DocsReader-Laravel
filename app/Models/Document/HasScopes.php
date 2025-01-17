<?php

namespace App\Models\Document;

use App\Models\User;
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
}

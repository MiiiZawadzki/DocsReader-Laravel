<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class UserStatisticsService
{
    /**
     * @param User $user
     * @param Carbon $date
     * @return array
     */
    public function readStatistics(User $user, Carbon $date): array
    {
        $documents = $user->userDocuments()
            ->whereHas('document', function (Builder $builder) use ($date) {
                $builder->whereDate('date_from', '<=', $date);
                $builder->whereNull('date_to')
                    ->orWhereDate('date_to', '>=', $date);
            });

        $read = $documents->clone()->whereHas('document', function (Builder $builder) use ($user) {
            $builder->whereHas('reads', function (Builder $builder) use ($user) {
                $builder->where('user_id', $user->getKey());
            });
        });

        return [
            [
                'value' => $read->count(),
                'name' => 'Read'
            ],
            [
                'value' => max($documents->count() - $read->count(), 0),
                'name' => 'Not read'
            ],
        ];
    }

    /**
     * @param User $user
     * @param Carbon $date
     * @return int
     */
    public function activeDocuments(User $user, Carbon $date): int
    {
        return Document::with(['userDocuments'])
            ->forUser($user)
            ->forDate($date)
            ->count();
    }

    /**
     * @param User $user
     * @return int
     */
    public function totalDocuments(User $user): int
    {
        return Document::with(['userDocuments'])
            ->forUser($user)
            ->count();
    }

    /**
     * @param User $user
     * @return int
     */
    public function readDocuments(User $user): int
    {
        return Document::with(['userDocuments'])
            ->forUser($user)
            ->whereHas('reads', function (Builder $builder) use ($user) {
                $builder->where('user_id', $user->getKey());
            })
            ->count();
    }
}

<?php

namespace App\Services;

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
}

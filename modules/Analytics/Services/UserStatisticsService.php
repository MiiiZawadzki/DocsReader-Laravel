<?php

namespace Modules\Analytics\Services;

class UserStatisticsService
{
    /**
     * @return array
     */
    public function readStatistics(): array
    {
//        $documents = $user->userDocuments()
//            ->whereHas('document', function (Builder $builder) use ($date) {
//                $builder->whereDate('date_from', '<=', $date);
//                $builder->whereNull('date_to')
//                    ->orWhereDate('date_to', '>=', $date);
//            });
//
//        $read = $documents->clone()->whereHas('document', function (Builder $builder) use ($user) {
//            $builder->whereHas('reads', function (Builder $builder) use ($user) {
//                $builder->where('user_id', $user->getKey());
//            });
//        });

        return [
            [
                'value' => 12,
                'name' => 'Read'
            ],
            [
                'value' => max(32 - 2, 0),
                'name' => 'Not read'
            ],
        ];
    }

    /**
     * @return int
     */
    public function activeDocuments(): int
    {
//        return Document::with(['userDocuments'])
//            ->forUser($user)
//            ->forDate($date)
//            ->count();
        return 14;
    }

    /**
     * @return int
     */
    public function totalDocuments(): int
    {
//        return Document::with(['userDocuments'])
//            ->forUser($user)
//            ->count();
        return 120;
    }

    /**
     * @return int
     */
    public function readDocuments(): int
    {
//        return Document::with(['userDocuments'])
//            ->forUser($user)
//            ->whereHas('reads', function (Builder $builder) use ($user) {
//                $builder->where('user_id', $user->getKey());
//            })
//            ->count();

        return 33;
    }
}

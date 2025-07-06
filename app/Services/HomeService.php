<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HomeService
{
    /**
     * @param User $user
     * @return Collection
     */
    public function data(User $user): Collection
    {
        $now = Carbon::now();

        $documents = $user->userDocuments()
            ->whereHas('document', function (Builder $builder) use ($now) {
                $builder->whereDate('date_from', '<=', $now);
                $builder->whereNull('date_to')
                    ->orWhereDate('date_to', '>=', $now);
            });

        $read = $documents->clone()->whereHas('document', function (Builder $builder) use ($user) {
            $builder->whereHas('reads', function (Builder $builder) use ($user) {
                $builder->where('user_id', $user->getKey());
            });
        });

        return collect([
            'total' => $documents->count(),
            'read' => $read->count(),
        ]);
    }
}

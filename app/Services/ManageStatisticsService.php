<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Carbon\Carbon;

class ManageStatisticsService
{
    /**
     * @param User $user
     * @param Carbon $date
     * @return array
     */
    public function readStatistics(User $user, Carbon $date): array
    {
        $usersAssigned = Document::with(['userDocuments'])
            ->forManager($user)
            ->get()
            ->map(fn(Document $document) => $document->userDocuments)
            ->flatten()
            ->count();


        $usersAssignedThatRead = Document::with(['reads'])
            ->forManager($user)
            ->get()
            ->map(fn(Document $document) => $document->reads)
            ->flatten()
            ->count();

        return [
            [
                'value' => $usersAssignedThatRead,
                'name' => 'Read'
            ],
            [
                'value' => max($usersAssigned - $usersAssignedThatRead, 0),
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
            ->forManager($user)
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
            ->forManager($user)
            ->count();
    }
}

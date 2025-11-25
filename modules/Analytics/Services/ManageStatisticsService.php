<?php

namespace Modules\Analytics\Services;

class ManageStatisticsService
{
    /**
     * @return array
     */
    public function readStatistics(): array
    {
//        $usersAssigned = Document::with(['userDocuments'])
//            ->forManager($user)
//            ->get()
//            ->map(fn(Document $document) => $document->userDocuments)
//            ->flatten()
//            ->count();
//
//
//        $usersAssignedThatRead = Document::with(['reads'])
//            ->forManager($user)
//            ->get()
//            ->map(fn(Document $document) => $document->reads)
//            ->flatten()
//            ->count();

        return [
            [
                'value' => 123,
                'name' => 'Read'
            ],
            [
                'value' => max(321 - 123, 0),
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
//            ->forManager($user)
//            ->forDate($date)
//            ->count();
        return 15;
    }

    /**
     * @return int
     */
    public function totalDocuments(): int
    {
//        return Document::with(['userDocuments'])
//            ->forManager($user)
//            ->count();
        return 100;
    }
}

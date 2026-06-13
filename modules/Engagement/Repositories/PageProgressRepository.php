<?php

namespace Modules\Engagement\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Engagement\Models\DocumentPageProgress;
use Modules\Engagement\Repositories\Contracts\PageProgressRepositoryInterface;

class PageProgressRepository implements PageProgressRepositoryInterface
{
    /**
     * @param  array  $entries
     * @return void
     */
    public function incrementBatch(array $entries): void
    {
        if (empty($entries)) {
            return;
        }

        // Seed any rows that don't exist yet
        $seed = array_map(fn ($entry) => [
            'user_id' => $entry['user_id'],
            'document_id' => $entry['document_id'],
            'page_number' => $entry['page_number'],
            'total_active_seconds' => 0,
            'first_viewed_at' => $entry['occurred_at'],
            'last_viewed_at' => $entry['occurred_at'],
        ], $entries);
        DB::table('document_page_progress')->insertOrIgnore($seed);

        // Apply each delta as a database-side increment rather than a PHP
        // read-modify-write. This is what keeps concurrent tick batches for the
        // same page from losing each other's seconds.
        foreach ($entries as $entry) {
            $keys = [
                'user_id' => $entry['user_id'],
                'document_id' => $entry['document_id'],
                'page_number' => $entry['page_number'],
            ];

            DB::table('document_page_progress')
                ->where($keys)
                ->update([
                    'total_active_seconds' => DB::raw('total_active_seconds + '.(int) $entry['add_seconds']),
                ]);

            // Advance last_viewed_at only when this tick is newer
            DB::table('document_page_progress')
                ->where($keys)
                ->where(function ($query) use ($entry) {
                    $query->whereNull('last_viewed_at')
                        ->orWhere('last_viewed_at', '<', $entry['occurred_at']);
                })
                ->update(['last_viewed_at' => $entry['occurred_at']]);
        }
    }

    /**
     * @param  int  $userId
     * @param  int  $documentId
     * @return Collection
     */
    public function forUserDocument(int $userId, int $documentId): Collection
    {
        return DocumentPageProgress::where('user_id', $userId)
            ->where('document_id', $documentId)
            ->orderBy('page_number')
            ->get();
    }

    /**
     * @param  int  $documentId
     * @return array|array[]
     */
    public function heatmapForDocument(int $documentId): array
    {
        return DB::table('document_page_progress')
            ->where('document_id', $documentId)
            ->groupBy('page_number')
            ->orderBy('page_number')
            ->selectRaw('page_number, AVG(total_active_seconds) AS avg_seconds, COUNT(DISTINCT user_id) AS viewers')
            ->get()
            ->map(fn ($row) => [
                'pageNumber' => (int) $row->page_number,
                'avgSeconds' => (int) round((float) $row->avg_seconds),
                'viewerCount' => (int) $row->viewers,
            ])
            ->all();
    }
}

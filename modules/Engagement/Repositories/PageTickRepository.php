<?php

namespace Modules\Engagement\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Engagement\Repositories\Contracts\PageTickRepositoryInterface;

class PageTickRepository implements PageTickRepositoryInterface
{
    /**
     * @param  array  $rows
     * @return int
     */
    public function insertIgnore(array $rows): int
    {
        if (empty($rows)) {
            return 0;
        }

        return DB::table('reading_page_ticks')->insertOrIgnore($rows);
    }

    /**
     * @param  array<string>  $sessionUuids
     * @return array<string, int>
     */
    public function maxPageBySessionUuids(array $sessionUuids): array
    {
        if (empty($sessionUuids)) {
            return [];
        }

        return DB::table('reading_page_ticks as t')
            ->join('reading_sessions as s', 's.id', '=', 't.reading_session_id')
            ->whereIn('s.uuid', $sessionUuids)
            ->groupBy('s.uuid')
            ->selectRaw('s.uuid, MAX(t.page_number) AS max_page')
            ->get()
            ->mapWithKeys(fn ($row) => [
                (string) $row->uuid => (int) $row->max_page,
            ])
            ->all();
    }
}

<?php

namespace Modules\Engagement\Repositories\Contracts;

use Illuminate\Support\Collection;

interface PageProgressRepositoryInterface
{
    /**
     *  Upsert per-page aggregate rows, incrementing total_active_seconds by the
     *  supplied amount and advancing last_viewed_at when newer.
     *
     * @param  array  $entries
     * @return void
     */
    public function incrementBatch(array $entries): void;

    /**
     *  Per-page totals for a single (user, document).
     *
     * @param  int  $userId
     * @param  int  $documentId
     * @return Collection
     */
    public function forUserDocument(int $userId, int $documentId): Collection;

    /**
     *  Per-page heatmap across all users.
     *
     * @param  int  $documentId
     * @return array
     */
    public function heatmapForDocument(int $documentId): array;
}

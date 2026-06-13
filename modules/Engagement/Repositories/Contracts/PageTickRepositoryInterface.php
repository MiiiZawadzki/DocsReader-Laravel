<?php

namespace Modules\Engagement\Repositories\Contracts;

interface PageTickRepositoryInterface
{
    /**
     * Bulk insert ticks
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return int Number of rows actually inserted (after dedupe).
     */
    public function insertIgnore(array $rows): int;

    /**
     * Furthest page reached by each session
     *
     * @param  array<string>  $sessionUuids
     * @return array<string, int> session uuid → max(page_number)
     */
    public function maxPageBySessionUuids(array $sessionUuids): array;
}

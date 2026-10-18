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
     * Which of the given client event ids are already recorded for the session
     *
     * @param  int  $sessionId
     * @param  array<string>  $clientEventIds
     * @return array<string>
     */
    public function existingClientEventIds(int $sessionId, array $clientEventIds): array;

    /**
     * Total active time already recorded for the session
     *
     * @param  int  $sessionId
     * @return int Milliseconds.
     */
    public function totalActiveMsForSession(int $sessionId): int;

    /**
     * Furthest page reached by each session
     *
     * @param  array<string>  $sessionUuids
     * @return array<string, int> session uuid → max(page_number)
     */
    public function maxPageBySessionUuids(array $sessionUuids): array;
}

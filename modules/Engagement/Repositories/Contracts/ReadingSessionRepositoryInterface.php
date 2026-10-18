<?php

namespace Modules\Engagement\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Engagement\Models\ReadingSession;

interface ReadingSessionRepositoryInterface
{
    /**
     * @param  int  $userId
     * @param  int  $documentId
     * @param  int  $resumePage
     * @param  array|null  $clientMeta
     * @return ReadingSession
     */
    public function create(int $userId, int $documentId, int $resumePage, ?array $clientMeta = null): ReadingSession;

    /**
     * @param  string  $uuid
     * @return ReadingSession|null
     */
    public function findByUuid(string $uuid): ?ReadingSession;

    /**
     * Re-read the session with a row lock.
     *
     * @param  int  $id
     * @return ReadingSession|null
     */
    public function findForUpdate(int $id): ?ReadingSession;

    /**
     * End every still-open session the user has on the document, except the given one.
     *
     * @param  int  $userId
     * @param  int  $documentId
     * @param  int  $exceptSessionId
     * @return int Number of sessions ended.
     */
    public function endOpenForUserDocument(int $userId, int $documentId, int $exceptSessionId): int;

    /**
     * @param  int  $userId
     * @param  int  $documentId
     * @return int
     */
    public function highestLastPage(int $userId, int $documentId): int;

    /**
     * @param  int  $userId
     * @param  int  $documentId
     * @return bool
     */
    public function hasAnyForUserDocument(int $userId, int $documentId): bool;

    /**
     * @param  ReadingSession  $session
     * @return void
     */
    public function markEnded(ReadingSession $session): void;

    /**
     * @return array{count: int, avgSeconds: int}
     */
    public function statsForDocument(int $documentId): array;

    /**
     * @param  int  $documentId
     * @param  int  $page
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function paginateForDocument(int $documentId, int $page, int $perPage): LengthAwarePaginator;

    /**
     * Resolve session ids to their uuids in bulk.
     *
     * @param  array<int>  $ids
     * @return array<int, string> id → uuid
     */
    public function uuidsByIds(array $ids): array;
}

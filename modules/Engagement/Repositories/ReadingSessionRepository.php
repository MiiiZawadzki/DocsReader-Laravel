<?php

namespace Modules\Engagement\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Engagement\Models\ReadingSession;
use Modules\Engagement\Repositories\Contracts\ReadingSessionRepositoryInterface;

class ReadingSessionRepository implements ReadingSessionRepositoryInterface
{
    /**
     * @param  int  $userId
     * @param  int  $documentId
     * @param  int  $resumePage
     * @param  array|null  $clientMeta
     * @return ReadingSession
     */
    public function create(int $userId, int $documentId, int $resumePage, ?array $clientMeta = null): ReadingSession
    {
        return ReadingSession::create([
            'uuid' => (string) Str::ulid(),
            'user_id' => $userId,
            'document_id' => $documentId,
            'started_at' => now(),
            'last_page' => max(1, $resumePage),
            'client_meta' => $clientMeta,
        ]);
    }

    /**
     * @param  string  $uuid
     * @return ReadingSession|null
     */
    public function findByUuid(string $uuid): ?ReadingSession
    {
        return ReadingSession::where('uuid', $uuid)->first();
    }

    /**
     * @param  int  $userId
     * @param  int  $documentId
     * @return int
     */
    public function highestLastPage(int $userId, int $documentId): int
    {
        return (int) (ReadingSession::where('user_id', $userId)
            ->where('document_id', $documentId)
            ->max('last_page') ?? 1);
    }

    /**
     * @param  int  $userId
     * @param  int  $documentId
     * @return bool
     */
    public function hasAnyForUserDocument(int $userId, int $documentId): bool
    {
        return ReadingSession::where('user_id', $userId)
            ->where('document_id', $documentId)
            ->exists();
    }

    /**
     * @param  ReadingSession  $session
     * @return void
     */
    public function markEnded(ReadingSession $session): void
    {
        if ($session->ended_at === null) {
            $session->ended_at = now();
            $session->save();
        }
    }

    /**
     * @param  int  $documentId
     * @return int[]
     */
    public function statsForDocument(int $documentId): array
    {
        $row = ReadingSession::where('document_id', $documentId)
            ->selectRaw('COUNT(*) AS count, AVG(total_active_seconds) AS avg_seconds')
            ->first();

        return [
            'count' => (int) ($row->count ?? 0),
            'avgSeconds' => (int) round((float) ($row->avg_seconds ?? 0)),
        ];
    }

    /**
     * @param  int  $documentId
     * @param  int  $page
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function paginateForDocument(int $documentId, int $page, int $perPage): LengthAwarePaginator
    {
        return ReadingSession::where('document_id', $documentId)
            ->orderByDesc('started_at')
            ->paginate(perPage: $perPage, page: $page);
    }

    public function uuidsByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return ReadingSession::whereIn('id', $ids)
            ->pluck('uuid', 'id')
            ->all();
    }
}

<?php

namespace Modules\History\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\History\Models\DocumentRead;
use Modules\History\Repositories\Contracts\DocumentReadRepositoryInterface;

class DocumentReadRepository implements DocumentReadRepositoryInterface
{
    /**
     * @param  int  $documentId
     * @param  int  $userId
     * @param  int|null  $sessionId
     * @param  int|null  $totalActiveSeconds
     * @param  int|null  $pagesViewedCount
     * @return DocumentRead
     */
    public function markAsRead(
        int $documentId,
        int $userId,
        ?int $sessionId = null,
        ?int $totalActiveSeconds = null,
        ?int $pagesViewedCount = null,
    ): DocumentRead {
        $now = now();

        $documentRead = DocumentRead::firstOrCreate(
            [
                'document_id' => $documentId,
                'user_id' => $userId,
                'confirmed' => true,
            ],
            [
                'certificate_id' => (string)Str::ulid(),
                'confirmed_at' => $now,
                'total_active_seconds' => $totalActiveSeconds,
                'pages_viewed_count' => $pagesViewedCount,
                'last_session_id' => $sessionId,
            ]
        );

        if (!$documentRead->wasRecentlyCreated) {
            $update = [
                'confirmed_at' => $now,
                'total_active_seconds' => $totalActiveSeconds ?? $documentRead->total_active_seconds,
                'pages_viewed_count' => $pagesViewedCount ?? $documentRead->pages_viewed_count,
                'last_session_id' => $sessionId ?? $documentRead->last_session_id,
            ];
            if (empty($documentRead->certificate_id)) {
                $update['certificate_id'] = (string)Str::ulid();
            }
            $documentRead->forceFill($update)->save();
        }

        return $documentRead;
    }

    /**
     * @param int $userId
     * @return Collection
     */
    public function getReadDocuments(int $userId): Collection
    {
        return DocumentRead::where('user_id', $userId)
            ->get();
    }

    /**
     * @param int $userId
     * @param array $documentIds
     * @return Collection
     */
    public function getReadStatusForDocuments(int $userId, array $documentIds): Collection
    {
        return DocumentRead::whereIn('document_id', $documentIds)
            ->where('user_id', $userId)
            ->get();
    }

    /**
     * @param int $userId
     * @param int $documentId
     * @return DocumentRead|null
     */
    public function getReadStatusForDocument(int $userId, int $documentId): ?DocumentRead
    {
        return DocumentRead::where('document_id', $documentId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * @param int $documentId
     * @return int
     */
    public function getDocumentReadCount(int $documentId): int
    {
        return DocumentRead::where('document_id', $documentId)->count();
    }

    /**
     * @param int $userId
     * @return int
     */
    public function getUserDocumentReadCount(int $userId): int
    {
        return DocumentRead::where('user_id', $userId)->count();
    }

    /**
     * @param array $documentsId
     * @return int
     */
    public function getDocumentsReadCount(array $documentsId): int
    {
        return DocumentRead::whereIn('document_id', $documentsId)->count();
    }

    /**
     * @param  int  $documentId
     * @param  int|null  $skimThreshold
     * @return array
     */
    public function aggregateConfirmedStatsForDocument(int $documentId, ?int $skimThreshold = null): array
    {
        $row = DocumentRead::where('document_id', $documentId)
            ->where('confirmed', true)
            ->selectRaw(
                'COUNT(*) AS confirmed_count, AVG(pages_viewed_count) AS avg_pages, AVG(total_active_seconds) AS avg_active'
            )
            ->first();

        $skimCount = 0;
        if ($skimThreshold !== null) {
            $skimCount = DocumentRead::where('document_id', $documentId)
                ->where('confirmed', true)
                ->whereNotNull('total_active_seconds')
                ->where('total_active_seconds', '<', $skimThreshold)
                ->count();
        }

        return [
            'confirmedCount' => (int)($row->confirmed_count ?? 0),
            'avgPagesViewed' => (int)round((float)($row->avg_pages ?? 0)),
            'avgActiveSeconds' => (int)round((float)($row->avg_active ?? 0)),
            'skimCount' => $skimCount,
        ];
    }

    /**
     * @param  int  $documentId
     * @param  array<int>  $userIds
     * @return array<int, array{confirmedAt: ?string, sessionId: ?int}>
     */
    public function confirmedReadsByUser(int $documentId, array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        return DocumentRead::where('document_id', $documentId)
            ->where('confirmed', true)
            ->whereIn('user_id', $userIds)
            ->get(['user_id', 'confirmed_at', 'last_session_id'])
            ->mapWithKeys(fn ($row) => [
                (int) $row->user_id => [
                    'confirmedAt' => $row->confirmed_at?->toIso8601String(),
                    'sessionId' => $row->last_session_id !== null ? (int) $row->last_session_id : null,
                ],
            ])
            ->all();
    }
}

<?php

namespace Modules\Engagement\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Engagement\Models\ReadingSession;
use Modules\Engagement\Repositories\Contracts\PageProgressRepositoryInterface;
use Modules\Engagement\Repositories\Contracts\PageTickRepositoryInterface;
use Modules\Engagement\Repositories\Contracts\ReadingSessionRepositoryInterface;

class EngagementRecorder
{
    public const MAX_TICK_MS = 15000;

    public function __construct(
        private readonly ReadingSessionRepositoryInterface $sessions,
        private readonly PageTickRepositoryInterface $ticks,
        private readonly PageProgressRepositoryInterface $progress,
    ) {}

    /**
     * @param  int  $userId
     * @param  int  $documentId
     * @param  array|null  $clientMeta
     * @return ReadingSession
     */
    public function start(int $userId, int $documentId, ?array $clientMeta = null): ReadingSession
    {
        $resumePage = $this->sessions->highestLastPage($userId, $documentId);

        return $this->sessions->create($userId, $documentId, $resumePage, $clientMeta);
    }

    /**
     * @param  ReadingSession  $session
     * @param  array<int, array{clientEventId: string, pageNumber: int, activeMs: int, occurredAt: string}>  $ticks
     * @return array{accepted: int, lastPage: int}
     */
    public function record(ReadingSession $session, array $ticks): array
    {
        $candidates = $this->sanitize($ticks);

        if (empty($candidates)) {
            return $this->unchanged($session);
        }

        return DB::transaction(function () use ($session, $candidates) {
            // Serialize batches of one session, so the duplicate check below sees each other's writes
            $locked = $this->sessions->findForUpdate($session->id) ?? $session;

            // A retried batch can mix already-recorded ticks with new ones, and only the new ones may be credited
            foreach ($this->ticks->existingClientEventIds($locked->id, array_keys($candidates)) as $knownId) {
                unset($candidates[$knownId]);
            }

            if (empty($candidates)) {
                return $this->unchanged($locked);
            }

            $now = now();
            $rows = [];
            $perPageMs = [];
            $perPageOccurredAt = [];
            $latestOccurredAt = null;
            $latestPage = $locked->last_page;

            foreach ($candidates as $clientEventId => $tick) {
                $rows[] = [
                    'reading_session_id' => $locked->id,
                    'user_id' => $locked->user_id,
                    'document_id' => $locked->document_id,
                    'page_number' => $tick['pageNumber'],
                    'client_event_id' => $clientEventId,
                    'active_ms' => $tick['activeMs'],
                    'occurred_at' => $tick['occurredAt'],
                    'created_at' => $now,
                ];

                // Accumulate raw milliseconds and truncate to whole seconds once per page (below), not per tick
                // Truncating each tick loses up to ~1s of genuine reading time per tick
                $page = $tick['pageNumber'];
                $perPageMs[$page] = ($perPageMs[$page] ?? 0) + $tick['activeMs'];

                $existing = $perPageOccurredAt[$page] ?? null;
                if ($existing === null || $tick['occurredAt']->greaterThan($existing)) {
                    $perPageOccurredAt[$page] = $tick['occurredAt'];
                }

                if ($latestOccurredAt === null || $tick['occurredAt']->greaterThan($latestOccurredAt)) {
                    $latestOccurredAt = $tick['occurredAt'];
                    $latestPage = $page;
                }
            }

            $inserted = $this->ticks->insertIgnore($rows);

            $entries = [];
            $totalAddSeconds = 0;
            foreach ($perPageMs as $page => $ms) {
                $seconds = intdiv($ms, 1000);
                if ($seconds <= 0) {
                    continue;
                }
                $totalAddSeconds += $seconds;
                $entries[] = [
                    'user_id' => $locked->user_id,
                    'document_id' => $locked->document_id,
                    'page_number' => $page,
                    'add_seconds' => $seconds,
                    'occurred_at' => $perPageOccurredAt[$page],
                ];
            }
            $this->progress->incrementBatch($entries);

            $locked->total_active_seconds = $locked->total_active_seconds + $totalAddSeconds;
            $locked->last_tick_at = $latestOccurredAt;
            $locked->last_page = $latestPage;
            $locked->save();

            return ['accepted' => $inserted, 'lastPage' => $locked->last_page];
        });
    }

    /**
     * @param  ReadingSession  $session
     * @return void
     */
    public function end(ReadingSession $session): void
    {
        $this->sessions->markEnded($session);
    }

    /**
     * Drop malformed ticks and repeats within the batch, keyed by client event id
     *
     * @param  array  $ticks
     * @return array<string, array{pageNumber: int, activeMs: int, occurredAt: Carbon}>
     */
    private function sanitize(array $ticks): array
    {
        $now = now();
        $candidates = [];

        foreach ($ticks as $tick) {
            $activeMs = (int) ($tick['activeMs'] ?? 0);
            $pageNumber = (int) ($tick['pageNumber'] ?? 0);
            $clientEventId = (string) ($tick['clientEventId'] ?? '');
            $occurredAtRaw = $tick['occurredAt'] ?? null;

            if ($activeMs <= 0 || $activeMs > self::MAX_TICK_MS) {
                continue;
            }
            if ($pageNumber < 1) {
                continue;
            }
            if (strlen($clientEventId) !== 26 || isset($candidates[$clientEventId])) {
                continue;
            }

            try {
                $occurredAt = $occurredAtRaw ? Carbon::parse($occurredAtRaw) : $now;
            } catch (\Throwable) {
                $occurredAt = $now;
            }

            $candidates[$clientEventId] = [
                'pageNumber' => $pageNumber,
                'activeMs' => $activeMs,
                'occurredAt' => $occurredAt,
            ];
        }

        return $candidates;
    }

    /**
     * @param  ReadingSession  $session
     * @return array{accepted: int, lastPage: int}
     */
    private function unchanged(ReadingSession $session): array
    {
        return ['accepted' => 0, 'lastPage' => $session->last_page];
    }
}

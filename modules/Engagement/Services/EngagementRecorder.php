<?php

namespace Modules\Engagement\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Document\Api\DocumentApiInterface;
use Modules\Engagement\Models\ReadingSession;
use Modules\Engagement\Repositories\Contracts\PageProgressRepositoryInterface;
use Modules\Engagement\Repositories\Contracts\PageTickRepositoryInterface;
use Modules\Engagement\Repositories\Contracts\ReadingSessionRepositoryInterface;

class EngagementRecorder
{
    public const MAX_TICK_MS = 15000;

    public const BUDGET_GRACE_MS = 15000;

    public function __construct(
        private readonly ReadingSessionRepositoryInterface $sessions,
        private readonly PageTickRepositoryInterface $ticks,
        private readonly PageProgressRepositoryInterface $progress,
        private readonly DocumentApiInterface $documentApi,
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

        $session = $this->sessions->create($userId, $documentId, $resumePage, $clientMeta);

        // One live session per user and document, so parallel tabs can't each claim the same wall-clock time
        $this->sessions->endOpenForUserDocument($userId, $documentId, $session->id);

        return $session;
    }

    /**
     * @param  ReadingSession  $session
     * @param  array<int, array{clientEventId: string, pageNumber: int, activeMs: int, occurredAt: string}>  $ticks
     * @return array{accepted: int, lastPage: int, ended: bool}
     */
    public function record(ReadingSession $session, array $ticks): array
    {
        if ($session->ended_at !== null) {
            return $this->unchanged($session, ended: true);
        }

        $totalPages = $this->documentApi->getDocumentById($session->document_id)?->totalPages;
        $candidates = $this->sanitize($ticks, $totalPages);

        if (empty($candidates)) {
            return $this->unchanged($session);
        }

        return DB::transaction(function () use ($session, $candidates) {
            // Serialize batches of one session, so the duplicate check and the time budget see each other's writes
            $locked = $this->sessions->findForUpdate($session->id);
            if ($locked === null || $locked->ended_at !== null) {
                return $this->unchanged($session, ended: true);
            }

            // A retried batch can mix already-recorded ticks with new ones, and only the new ones may be credited
            foreach ($this->ticks->existingClientEventIds($locked->id, array_keys($candidates)) as $knownId) {
                unset($candidates[$knownId]);
            }

            $now = now();
            $accepted = $this->withinTimeBudget($locked, $candidates, $now);

            if (empty($accepted)) {
                return $this->unchanged($locked);
            }

            $rows = [];
            $perPageMs = [];
            $perPageOccurredAt = [];
            $latestOccurredAt = null;
            $latestPage = $locked->last_page;

            foreach ($accepted as $clientEventId => $tick) {
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
            // Server receive time, the client's occurredAt is not trusted for anything but ordering
            $locked->last_tick_at = $now;
            $locked->last_page = $latestPage;
            $locked->save();

            return ['accepted' => $inserted, 'lastPage' => $locked->last_page, 'ended' => false];
        });
    }

    /**
     * @param  ReadingSession  $session
     * @param  array<int, array{clientEventId: string, pageNumber: int, activeMs: int, occurredAt: string}>  $ticks
     * @return int Number of final ticks credited
     */
    public function end(ReadingSession $session, array $ticks = []): int
    {
        // Final ticks arrive with the end itself, so they can't race a separate end request and be rejected as late
        $accepted = empty($ticks) ? 0 : $this->record($session, $ticks)['accepted'];

        $this->sessions->markEnded($session);

        return $accepted;
    }

    /**
     * Drop malformed ticks, repeats within the batch and pages the document doesn't have, keyed by client event id
     *
     * @param  array  $ticks
     * @param  int|null  $totalPages
     * @return array<string, array{pageNumber: int, activeMs: int, occurredAt: Carbon}>
     */
    private function sanitize(array $ticks, ?int $totalPages): array
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
            if ($pageNumber < 1 || ($totalPages !== null && $pageNumber > $totalPages)) {
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
     * Keep ticks, in batch order, while their total fits the wall-clock time the session has been open
     *
     * @param  ReadingSession  $session
     * @param  array<string, array{pageNumber: int, activeMs: int, occurredAt: Carbon}>  $candidates
     * @param  Carbon  $now
     * @return array<string, array{pageNumber: int, activeMs: int, occurredAt: Carbon}>
     */
    private function withinTimeBudget(ReadingSession $session, array $candidates, Carbon $now): array
    {
        $elapsedMs = (int) max(0, $session->started_at->diffInMilliseconds($now));
        $remainingMs = $elapsedMs + self::BUDGET_GRACE_MS - $this->ticks->totalActiveMsForSession($session->id);

        $accepted = [];
        foreach ($candidates as $clientEventId => $tick) {
            if ($tick['activeMs'] > $remainingMs) {
                break;
            }
            $remainingMs -= $tick['activeMs'];
            $accepted[$clientEventId] = $tick;
        }

        return $accepted;
    }

    /**
     * @param  ReadingSession  $session
     * @param  bool  $ended
     * @return array{accepted: int, lastPage: int, ended: bool}
     */
    private function unchanged(ReadingSession $session, bool $ended = false): array
    {
        return ['accepted' => 0, 'lastPage' => $session->last_page, 'ended' => $ended];
    }
}

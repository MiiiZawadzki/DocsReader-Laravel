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
        if (empty($ticks)) {
            return ['accepted' => 0, 'lastPage' => $session->last_page];
        }

        $now = now();
        $rows = [];
        $perPageMs = [];
        $perPageOccurredAt = [];
        $latestOccurredAt = null;
        $latestPage = $session->last_page;

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
            if (strlen($clientEventId) !== 26) {
                continue;
            }

            try {
                $occurredAt = $occurredAtRaw ? Carbon::parse($occurredAtRaw) : $now;
            } catch (\Throwable) {
                $occurredAt = $now;
            }

            $rows[] = [
                'reading_session_id' => $session->id,
                'user_id' => $session->user_id,
                'document_id' => $session->document_id,
                'page_number' => $pageNumber,
                'client_event_id' => $clientEventId,
                'active_ms' => $activeMs,
                'occurred_at' => $occurredAt,
                'created_at' => $now,
            ];

            // Accumulate raw milliseconds and truncate to whole seconds once per page (below), not per tick
            // Truncating each tick loses up to ~1s of genuine reading time per tick
            $perPageMs[$pageNumber] = ($perPageMs[$pageNumber] ?? 0) + $activeMs;

            $existing = $perPageOccurredAt[$pageNumber] ?? null;
            if ($existing === null || $occurredAt->greaterThan($existing)) {
                $perPageOccurredAt[$pageNumber] = $occurredAt;
            }

            if ($latestOccurredAt === null || $occurredAt->greaterThan($latestOccurredAt)) {
                $latestOccurredAt = $occurredAt;
                $latestPage = $pageNumber;
            }
        }

        if (empty($rows)) {
            return ['accepted' => 0, 'lastPage' => $session->last_page];
        }

        return DB::transaction(
            function () use ($session, $rows, $perPageMs, $perPageOccurredAt, $latestOccurredAt, $latestPage) {
                $inserted = $this->ticks->insertIgnore($rows);

                // No new rows means every tick in this batch was already recorded
                if ($inserted === 0) {
                    return ['accepted' => 0, 'lastPage' => $session->last_page];
                }

                $entries = [];
                $totalAddSeconds = 0;
                foreach ($perPageMs as $page => $ms) {
                    $seconds = intdiv($ms, 1000);
                    if ($seconds <= 0) {
                        continue;
                    }
                    $totalAddSeconds += $seconds;
                    $entries[] = [
                        'user_id' => $session->user_id,
                        'document_id' => $session->document_id,
                        'page_number' => $page,
                        'add_seconds' => $seconds,
                        'occurred_at' => $perPageOccurredAt[$page],
                    ];
                }
                $this->progress->incrementBatch($entries);

                $session->total_active_seconds = $session->total_active_seconds + $totalAddSeconds;
                $session->last_tick_at = $latestOccurredAt;
                $session->last_page = $latestPage;
                $session->save();

                return ['accepted' => $inserted, 'lastPage' => $session->last_page];
            }
        );
    }

    /**
     * @param  ReadingSession  $session
     * @return void
     */
    public function end(ReadingSession $session): void
    {
        $this->sessions->markEnded($session);
    }
}

<?php

namespace Modules\Engagement\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Modules\Document\Models\Document;
use Modules\Engagement\Models\DocumentPageProgress;
use Modules\Engagement\Models\ReadingPageTick;
use Modules\Engagement\Models\ReadingSession;
use Modules\Engagement\Repositories\Contracts\ReadingSessionRepositoryInterface;
use Modules\Engagement\Services\EngagementRecorder;
use Modules\User\DTO\UserDTO;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('feature')]
#[Group('Engagement')]
class EngagementRecorderTest extends FeatureTestCase
{
    public function test_recording_ticks_aggregates_seconds_and_page_progress(): void
    {
        [$user, $document] = $this->makeUserAndDocument();
        $session = $this->createSession($user->getId(), $document->id);

        $result = $this->recorder()->record($session, [
            $this->tick(pageNumber: 1, activeMs: 5000, occurredAt: now()->subSeconds(10)),
            $this->tick(pageNumber: 2, activeMs: 3000, occurredAt: now()),
        ]);

        $this->assertSame(2, $result['accepted']);
        $this->assertSame(2, $result['lastPage']);

        $this->assertSame(8, (int) $session->fresh()->total_active_seconds);
        $this->assertSame(5, $this->pageSeconds($user->getId(), $document->id, 1));
        $this->assertSame(3, $this->pageSeconds($user->getId(), $document->id, 2));
    }

    public function test_duplicate_tick_batch_is_idempotent(): void
    {
        [$user, $document] = $this->makeUserAndDocument();
        $session = $this->createSession($user->getId(), $document->id);

        $ticks = [$this->tick(pageNumber: 1, activeMs: 5000)];

        $first = $this->recorder()->record($session, $ticks);
        // Same clientEventId(s) — e.g. a sendBeacon retry. Must not double-count.
        $second = $this->recorder()->record($session->fresh(), $ticks);

        $this->assertSame(1, $first['accepted']);
        $this->assertSame(0, $second['accepted']);

        $this->assertSame(1, ReadingPageTick::count());
        $this->assertSame(5, (int) $session->fresh()->total_active_seconds);
        $this->assertSame(5, $this->pageSeconds($user->getId(), $document->id, 1));
    }

    public function test_separate_batches_accumulate_page_progress(): void
    {
        [$user, $document] = $this->makeUserAndDocument();
        $session = $this->createSession($user->getId(), $document->id);

        $this->recorder()->record($session, [$this->tick(pageNumber: 1, activeMs: 4000)]);
        $this->recorder()->record($session->fresh(), [$this->tick(pageNumber: 1, activeMs: 6000)]);

        // Additive upsert: the second batch must add to, not overwrite, the first.
        $this->assertSame(10, $this->pageSeconds($user->getId(), $document->id, 1));
        $this->assertSame(1, DocumentPageProgress::count());
    }

    public function test_sub_second_ticks_are_summed_before_truncation(): void
    {
        [$user, $document] = $this->makeUserAndDocument();
        $session = $this->createSession($user->getId(), $document->id);

        // Four 1500ms ticks on the same page = 6000ms of genuine reading.
        // Truncating per tick would credit 1+1+1+1 = 4s; summing first gives 6s.
        $this->recorder()->record($session, [
            $this->tick(pageNumber: 1, activeMs: 1500),
            $this->tick(pageNumber: 1, activeMs: 1500),
            $this->tick(pageNumber: 1, activeMs: 1500),
            $this->tick(pageNumber: 1, activeMs: 1500),
        ]);

        $this->assertSame(6, $this->pageSeconds($user->getId(), $document->id, 1));
        $this->assertSame(6, (int) $session->fresh()->total_active_seconds);
    }

    public function test_partially_duplicated_batch_only_credits_the_new_ticks(): void
    {
        [$user, $document] = $this->makeUserAndDocument();
        $session = $this->createSession($user->getId(), $document->id);

        $first = $this->tick(pageNumber: 1, activeMs: 5000);
        $this->recorder()->record($session, [$first]);

        // A failed flush re-sends the old tick at the head of the next batch
        $result = $this->recorder()->record($session->fresh(), [
            $first,
            $this->tick(pageNumber: 1, activeMs: 3000),
        ]);

        $this->assertSame(1, $result['accepted']);
        $this->assertSame(2, ReadingPageTick::count());
        $this->assertSame(8, $this->pageSeconds($user->getId(), $document->id, 1));
        $this->assertSame(8, (int) $session->fresh()->total_active_seconds);
    }

    public function test_tick_repeated_within_one_batch_is_credited_once(): void
    {
        [$user, $document] = $this->makeUserAndDocument();
        $session = $this->createSession($user->getId(), $document->id);

        $tick = $this->tick(pageNumber: 1, activeMs: 5000);
        $result = $this->recorder()->record($session, [$tick, $tick]);

        $this->assertSame(1, $result['accepted']);
        $this->assertSame(5, $this->pageSeconds($user->getId(), $document->id, 1));
        $this->assertSame(5, (int) $session->fresh()->total_active_seconds);
    }

    public function test_ticks_for_pages_beyond_the_document_are_dropped(): void
    {
        [$user, $document] = $this->makeUserAndDocument(totalPages: 2);
        $session = $this->createSession($user->getId(), $document->id);

        $result = $this->recorder()->record($session, [
            $this->tick(pageNumber: 2, activeMs: 4000),
            $this->tick(pageNumber: 3, activeMs: 4000),
        ]);

        $this->assertSame(1, $result['accepted']);
        $this->assertSame(0, $this->pageSeconds($user->getId(), $document->id, 3));
    }

    public function test_credited_time_cannot_exceed_the_time_the_session_has_been_open(): void
    {
        [$user, $document] = $this->makeUserAndDocument();
        $session = $this->createSession($user->getId(), $document->id);

        $this->travel(10)->seconds();

        // 10s open plus the one-off grace allows 25s; the rest of the minute claimed here is refused
        $ticks = [];
        for ($i = 0; $i < 6; $i++) {
            $ticks[] = $this->tick(pageNumber: 1, activeMs: 10000);
        }
        $result = $this->recorder()->record($session, $ticks);

        $this->assertSame(2, $result['accepted']);
        $this->assertSame(20, $this->pageSeconds($user->getId(), $document->id, 1));
    }

    public function test_grace_is_granted_once_per_session_not_per_batch(): void
    {
        [$user, $document] = $this->makeUserAndDocument();
        $session = $this->createSession($user->getId(), $document->id);

        // Rapid-fire batches with no time passing can't each claim a fresh grace
        for ($i = 0; $i < 5; $i++) {
            $this->recorder()->record($session->fresh(), [$this->tick(pageNumber: 1, activeMs: 5000)]);
        }

        $this->assertSame(15, $this->pageSeconds($user->getId(), $document->id, 1));
    }

    public function test_starting_fresh_sessions_does_not_grant_a_fresh_grace_each_time(): void
    {
        [$user, $document] = $this->makeUserAndDocument();

        // Restarting the session and claiming its grace straight away, over and over, with no time passing
        for ($i = 0; $i < 5; $i++) {
            $session = $this->recorder()->start($user->getId(), $document->id);
            $this->recorder()->record($session, [$this->tick(pageNumber: 1, activeMs: 15000)]);
        }

        $this->assertSame(15, $this->pageSeconds($user->getId(), $document->id, 1));
    }

    public function test_reading_two_documents_at_once_shares_one_clock(): void
    {
        [$user, $first] = $this->makeUserAndDocument();
        [, $second] = $this->makeUserAndDocument(owner: $user);
        $firstSession = $this->createSession($user->getId(), $first->id);
        $secondSession = $this->createSession($user->getId(), $second->id);

        $this->travel(30)->seconds();

        // 30s of wall-clock time plus the one-off grace is 45s in total, however it's split between documents
        $this->recorder()->record($firstSession, array_map(fn() => $this->tick(pageNumber: 1, activeMs: 5000), range(1, 6)));
        $this->recorder()->record($secondSession, array_map(fn() => $this->tick(pageNumber: 1, activeMs: 5000), range(1, 6)));

        $this->assertSame(30, $this->pageSeconds($user->getId(), $first->id, 1));
        $this->assertSame(15, $this->pageSeconds($user->getId(), $second->id, 1));
    }

    public function test_session_started_after_a_pause_gets_its_grace_again(): void
    {
        [$user, $document] = $this->makeUserAndDocument();
        $first = $this->createSession($user->getId(), $document->id);
        $this->recorder()->record($first, [$this->tick(pageNumber: 1, activeMs: 15000)]);

        $this->travel(1)->hours();
        $second = $this->createSession($user->getId(), $document->id);
        $result = $this->recorder()->record($second, [$this->tick(pageNumber: 1, activeMs: 15000)]);

        $this->assertSame(1, $result['accepted']);
        $this->assertSame(30, $this->pageSeconds($user->getId(), $document->id, 1));
    }

    public function test_ended_session_accepts_no_ticks(): void
    {
        [$user, $document] = $this->makeUserAndDocument();
        $session = $this->createSession($user->getId(), $document->id);
        $this->recorder()->end($session);

        $result = $this->recorder()->record($session->fresh(), [$this->tick(pageNumber: 1, activeMs: 5000)]);

        $this->assertTrue($result['ended']);
        $this->assertSame(0, ReadingPageTick::count());
    }

    public function test_ending_a_session_credits_the_final_ticks_sent_with_it(): void
    {
        [$user, $document] = $this->makeUserAndDocument();
        $session = $this->createSession($user->getId(), $document->id);
        $this->travel(10)->seconds();

        $accepted = $this->recorder()->end($session, [$this->tick(pageNumber: 1, activeMs: 5000)]);

        $this->assertSame(1, $accepted);
        $this->assertSame(5, $this->pageSeconds($user->getId(), $document->id, 1));
        $this->assertNotNull($session->fresh()->ended_at);
    }

    public function test_starting_a_session_ends_the_users_other_open_sessions_on_the_document(): void
    {
        [$user, $document] = $this->makeUserAndDocument();

        $older = $this->recorder()->start($user->getId(), $document->id);
        $newer = $this->recorder()->start($user->getId(), $document->id);

        $this->assertNotNull($older->fresh()->ended_at);
        $this->assertNull($newer->fresh()->ended_at);
    }


    private function recorder(): EngagementRecorder
    {
        return app(EngagementRecorder::class);
    }

    /**
     * @param  int|null  $totalPages
     * @param  UserDTO|null  $owner  Reuse an existing user instead of creating one
     * @return array{0: UserDTO, 1: Document}
     */
    private function makeUserAndDocument(?int $totalPages = null, ?UserDTO $owner = null): array
    {
        $user = $owner ?? $this->makeUser();
        $document = Document::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test document',
            'source_name' => 'test.pdf',
            'description' => 'Test description',
            'user_id' => $user->getId(),
            'file_path' => '/uploads/test/test.pdf',
            'date_from' => now(),
            'date_to' => null,
            'declaration_message' => null,
            'delay' => 0,
            'total_pages' => $totalPages,
        ]);

        return [$user, $document];
    }

    private function createSession(int $userId, int $documentId): ReadingSession
    {
        return app(ReadingSessionRepositoryInterface::class)
            ->create($userId, $documentId, 1);
    }

    /**
     * @return array{clientEventId: string, pageNumber: int, activeMs: int, occurredAt: string}
     */
    private function tick(int $pageNumber, int $activeMs, ?Carbon $occurredAt = null): array
    {
        return [
            'clientEventId' => (string) Str::ulid(),
            'pageNumber' => $pageNumber,
            'activeMs' => $activeMs,
            'occurredAt' => ($occurredAt ?? now())->toIso8601String(),
        ];
    }

    private function pageSeconds(int $userId, int $documentId, int $page): int
    {
        return (int) DocumentPageProgress::where('user_id', $userId)
            ->where('document_id', $documentId)
            ->where('page_number', $page)
            ->value('total_active_seconds');
    }
}

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


    private function recorder(): EngagementRecorder
    {
        return app(EngagementRecorder::class);
    }

    /**
     * @return array{0: UserDTO, 1: Document}
     */
    private function makeUserAndDocument(): array
    {
        $user = $this->makeUser();
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

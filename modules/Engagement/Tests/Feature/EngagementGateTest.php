<?php

namespace Modules\Engagement\Tests\Feature;

use Illuminate\Support\Str;
use Modules\Document\Models\Document;
use Modules\Engagement\Repositories\Contracts\PageProgressRepositoryInterface;
use Modules\Engagement\Repositories\Contracts\ReadingSessionRepositoryInterface;
use Modules\Engagement\Services\EngagementGate;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('feature')]
#[Group('Engagement')]
class EngagementGateTest extends FeatureTestCase
{
    public function test_unknown_document_is_denied(): void
    {
        $user = $this->makeUser();

        $result = $this->gate()->evaluate($user->getId(), '00000000-0000-0000-0000-000000000000');

        $this->assertFalse($result['allowed']);
    }

    public function test_user_without_a_session_is_allowed_through(): void
    {
        $user = $this->makeUser();
        $document = $this->makeDocument($user->getId(), totalPages: 3, delay: 5);

        // Backward-compat: never started a session → not gated.
        $result = $this->gate()->evaluate($user->getId(), $document->uuid);

        $this->assertTrue($result['allowed']);
    }

    public function test_session_with_unmet_pages_is_denied(): void
    {
        $user = $this->makeUser();
        $document = $this->makeDocument($user->getId(), totalPages: 2, delay: 5);
        $this->sessions()->create($user->getId(), $document->id, 1);

        // Only page 1 meets the 5s threshold; page 2 is untouched.
        $this->givePageSeconds($user->getId(), $document->id, page: 1, seconds: 6);

        $result = $this->gate()->evaluate($user->getId(), $document->uuid);

        $this->assertFalse($result['allowed']);
        $this->assertSame([2], $result['missingPages']);
    }

    public function test_session_with_all_pages_met_is_allowed(): void
    {
        $user = $this->makeUser();
        $document = $this->makeDocument($user->getId(), totalPages: 2, delay: 5);
        $this->sessions()->create($user->getId(), $document->id, 1);

        $this->givePageSeconds($user->getId(), $document->id, page: 1, seconds: 6);
        $this->givePageSeconds($user->getId(), $document->id, page: 2, seconds: 5);

        $result = $this->gate()->evaluate($user->getId(), $document->uuid);

        $this->assertTrue($result['allowed']);
        $this->assertSame([], $result['missingPages']);
    }


    private function gate(): EngagementGate
    {
        return app(EngagementGate::class);
    }

    private function sessions(): ReadingSessionRepositoryInterface
    {
        return app(ReadingSessionRepositoryInterface::class);
    }

    private function givePageSeconds(int $userId, int $documentId, int $page, int $seconds): void
    {
        app(PageProgressRepositoryInterface::class)->incrementBatch([[
            'user_id' => $userId,
            'document_id' => $documentId,
            'page_number' => $page,
            'add_seconds' => $seconds,
            'occurred_at' => now(),
        ]]);
    }

    private function makeDocument(int $userId, int $totalPages, int $delay): Document
    {
        return Document::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test document',
            'source_name' => 'test.pdf',
            'description' => 'Test description',
            'user_id' => $userId,
            'file_path' => '/uploads/test/test.pdf',
            'date_from' => now(),
            'date_to' => null,
            'declaration_message' => null,
            'delay' => $delay,
            'total_pages' => $totalPages,
        ]);
    }
}

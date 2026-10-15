<?php

namespace Modules\History\Tests\Feature;

use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Modules\Document\Models\Document;
use Modules\Document\Models\UserDocument;
use Modules\History\Models\DocumentRead;
use Modules\User\DTO\UserDTO;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('feature')]
#[Group('History')]
class ConfirmReadingTest extends FeatureTestCase
{
    public function test_reader_who_read_every_page_can_confirm(): void
    {
        $user = $this->loginAsRegularUser();
        $document = $this->assignedDocument($user->getId());

        $sessionUuid = $this->startReadingSession($document)->assertStatus(200)->json('data.session_uuid');
        $this->travel(15)->seconds();
        $this->sendTicks($sessionUuid, [1 => 5000, 2 => 5000])->assertStatus(202);

        $response = $this->confirm($document, $sessionUuid)->assertStatus(200);

        $this->assertNotEmpty($response->json('certificateId'));
        $read = DocumentRead::firstOrFail();
        $this->assertSame(10, $read->total_active_seconds);
        $this->assertNotNull($read->last_session_id);
    }

    public function test_reader_who_never_opened_the_document_cannot_confirm(): void
    {
        $user = $this->loginAsRegularUser();
        $document = $this->assignedDocument($user->getId());

        $this->confirm($document)
            ->assertStatus(422)
            ->assertJsonPath('reason', 'no_session')
            ->assertJsonPath('missing_pages', [1, 2]);

        $this->assertSame(0, DocumentRead::count());
    }

    public function test_reader_who_skipped_a_page_cannot_confirm(): void
    {
        $user = $this->loginAsRegularUser();
        $document = $this->assignedDocument($user->getId());

        $sessionUuid = $this->startReadingSession($document)->json('data.session_uuid');
        $this->travel(15)->seconds();
        $this->sendTicks($sessionUuid, [1 => 5000]);

        $this->confirm($document, $sessionUuid)
            ->assertStatus(422)
            ->assertJsonPath('reason', 'incomplete')
            ->assertJsonPath('missing_pages', [2]);
    }

    public function test_document_with_unknown_page_count_cannot_be_confirmed(): void
    {
        $user = $this->loginAsRegularUser();
        $document = $this->assignedDocument($user->getId(), ['total_pages' => null]);

        $sessionUuid = $this->startReadingSession($document)->json('data.session_uuid');
        $this->travel(15)->seconds();
        $this->sendTicks($sessionUuid, [1 => 5000, 2 => 5000]);

        $this->confirm($document, $sessionUuid)
            ->assertStatus(422)
            ->assertJsonPath('reason', 'page_count_unknown')
            ->assertJsonPath('message', __('engagement::messages.gate.page_count_unknown'));
    }

    public function test_document_without_a_reading_minimum_can_be_confirmed_directly(): void
    {
        $user = $this->loginAsRegularUser();
        $document = $this->assignedDocument($user->getId(), ['delay' => 0]);

        $this->confirm($document)->assertStatus(200);
    }

    private function loginAsRegularUser(): UserDTO
    {
        $user = $this->makeUser();
        $this->postJson('/api/login', [
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
        ])->assertStatus(200);

        return $user;
    }

    private function assignedDocument(int $userId, array $attributes = []): Document
    {
        $document = $this->makeDocument($userId, $attributes);
        UserDocument::create(['document_id' => $document->id, 'user_id' => $userId, 'created_by' => $userId]);

        return $document;
    }

    private function makeDocument(int $managerId, array $attributes = []): Document
    {
        return Document::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'name' => 'Security policy',
            'source_name' => 'policy.pdf',
            'description' => 'Test description',
            'user_id' => $managerId,
            'file_path' => '/uploads/test/policy.pdf',
            'date_from' => now()->subDay(),
            'date_to' => null,
            'requires_confirmation' => true,
            'declaration_message' => 'I have read and understood this policy.',
            'delay' => 5,
            'total_pages' => 2,
        ], $attributes));
    }

    private function startReadingSession(Document $document): TestResponse
    {
        return $this->postJson('/api/reading-sessions', ['documentUuid' => $document->uuid]);
    }

    /**
     * @param  string  $sessionUuid
     * @param  array<int, int>  $msByPage
     * @return TestResponse
     */
    private function sendTicks(string $sessionUuid, array $msByPage): TestResponse
    {
        $ticks = [];
        foreach ($msByPage as $page => $ms) {
            $ticks[] = [
                'clientEventId' => (string) Str::ulid(),
                'pageNumber' => $page,
                'activeMs' => $ms,
                'occurredAt' => now()->toIso8601String(),
            ];
        }

        return $this->postJson('/api/reading-sessions/'.$sessionUuid.'/ticks', ['ticks' => $ticks]);
    }

    private function confirm(Document $document, ?string $sessionUuid = null): TestResponse
    {
        return $this->postJson('/api/document-reads/'.$document->uuid, ['session_uuid' => $sessionUuid]);
    }
}

<?php

namespace Modules\Document\Tests\Feature;

use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Modules\Document\Models\Document;
use Modules\Document\Models\UserDocument;
use Modules\User\DTO\UserDTO;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('feature')]
#[Group('Document')]
class DocumentValidityWindowTest extends FeatureTestCase
{
    public function test_active_document_can_be_read_and_confirmed(): void
    {
        $user = $this->loginAsRegularUser();
        $document = $this->assignedDocument($user->getId());

        $this->startReadingSession($document)->assertStatus(200);
        $this->confirm($document)->assertStatus(200);
    }

    public function test_window_boundaries_are_inclusive(): void
    {
        $user = $this->loginAsRegularUser();
        $startsToday = $this->assignedDocument($user->getId(), ['date_from' => now()->startOfDay()]);
        $endsToday = $this->assignedDocument($user->getId(), [
            'date_from' => now()->subMonth(),
            'date_to' => now()->startOfDay(),
        ]);

        $this->startReadingSession($startsToday)->assertStatus(200);
        $this->startReadingSession($endsToday)->assertStatus(200);
    }

    public function test_future_document_cannot_be_read_or_confirmed(): void
    {
        $user = $this->loginAsRegularUser();
        $document = $this->assignedDocument($user->getId(), ['date_from' => now()->addWeek()]);

        $this->startReadingSession($document)->assertStatus(403);
        $this->confirm($document)->assertStatus(403);
    }

    public function test_future_document_with_an_end_date_cannot_be_read_or_confirmed(): void
    {
        $user = $this->loginAsRegularUser();
        $document = $this->assignedDocument($user->getId(), [
            'date_from' => now()->addWeek(),
            'date_to' => now()->addMonth(),
        ]);

        $this->startReadingSession($document)->assertStatus(403);
        $this->confirm($document)->assertStatus(403);
    }

    public function test_expired_document_cannot_be_read_or_confirmed(): void
    {
        $user = $this->loginAsRegularUser();
        $document = $this->assignedDocument($user->getId(), [
            'date_from' => now()->subMonth(),
            'date_to' => now()->subDay(),
        ]);

        $this->startReadingSession($document)->assertStatus(403);
        $this->confirm($document)->assertStatus(403);
    }

    public function test_expired_document_can_still_be_viewed(): void
    {
        $user = $this->loginAsRegularUser();
        $document = $this->assignedDocument($user->getId(), [
            'date_from' => now()->subMonth(),
            'date_to' => now()->subDay(),
        ]);

        $this->getJson('/api/documents/'.$document->uuid)->assertStatus(200);
    }

    public function test_unassigned_document_cannot_be_viewed_read_or_confirmed(): void
    {
        $this->loginAsRegularUser();
        $owner = $this->makeUser(['name' => 'Owner', 'email' => 'owner@example.com', 'password' => bcrypt('x')]);
        $document = $this->makeDocument($owner->getId());

        $this->getJson('/api/documents/'.$document->uuid)->assertStatus(403);
        $this->startReadingSession($document)->assertStatus(403);
        $this->confirm($document)->assertStatus(403);
    }

    public function test_deleted_document_cannot_be_viewed_read_or_confirmed(): void
    {
        $user = $this->loginAsRegularUser();
        $document = $this->assignedDocument($user->getId());
        $document->delete();

        $this->getJson('/api/documents/'.$document->uuid)->assertStatus(403);
        $this->startReadingSession($document)->assertStatus(403);
        $this->confirm($document)->assertStatus(403);
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
            'declaration_message' => null,
            'delay' => 0,
            'total_pages' => 2,
        ], $attributes));
    }

    private function startReadingSession(Document $document): TestResponse
    {
        return $this->postJson('/api/reading-sessions', ['documentUuid' => $document->uuid]);
    }

    private function confirm(Document $document): TestResponse
    {
        return $this->postJson('/api/document-reads/'.$document->uuid);
    }
}

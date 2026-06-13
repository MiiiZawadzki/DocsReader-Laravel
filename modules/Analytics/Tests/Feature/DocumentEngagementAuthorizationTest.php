<?php

namespace Modules\Analytics\Tests\Feature;

use Illuminate\Support\Str;
use Modules\Access\Models\Permission;
use Modules\Access\Models\UserPermission;
use Modules\Document\Models\Document;
use Modules\User\DTO\UserDTO;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('feature')]
#[Group('Analytics')]
class DocumentEngagementAuthorizationTest extends FeatureTestCase
{
    public function test_unauthenticated_user_is_rejected_with_401(): void
    {
        $this->getJson('/api/statistics/manage/document/'.$this->fakeUuid().'/engagement/summary')
            ->assertStatus(401);
    }

    public function test_user_without_manage_documents_permission_is_forbidden(): void
    {
        $this->loginAsRegularUser();

        $this->getJson('/api/statistics/manage/document/'.$this->fakeUuid().'/engagement/summary')
            ->assertStatus(403);
    }

    public function test_permissioned_user_cannot_view_someone_elses_document_engagement(): void
    {
        $owner = $this->makeUser(['name' => 'Owner', 'email' => 'owner@example.com', 'password' => bcrypt('x')]);
        $document = $this->createDocumentForManager($owner->getId());

        $intruder = $this->loginAsRegularUser();
        $this->grantManageDocuments($intruder->getId());

        $this->getJson('/api/statistics/manage/document/'.$document->uuid.'/engagement/summary')
            ->assertStatus(403);
        $this->getJson('/api/statistics/manage/document/'.$document->uuid.'/engagement/heatmap')
            ->assertStatus(403);
        $this->getJson('/api/statistics/manage/document/'.$document->uuid.'/engagement/sessions')
            ->assertStatus(403);
    }

    public function test_manager_with_permission_can_view_engagement_summary(): void
    {
        $user = $this->loginAsRegularUser();
        $this->grantManageDocuments($user->getId());
        $document = $this->createDocumentForManager($user->getId());

        $this->getJson('/api/statistics/manage/document/'.$document->uuid.'/engagement/summary')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['total_sessions', 'completion_rate', 'skim_rate', 'total_pages']]);
    }

    public function test_sessions_rejects_oversized_per_page(): void
    {
        $user = $this->loginAsRegularUser();
        $this->grantManageDocuments($user->getId());
        $document = $this->createDocumentForManager($user->getId());

        $this->getJson('/api/statistics/manage/document/'.$document->uuid.'/engagement/sessions?per_page=10000')
            ->assertStatus(422);
    }

    public function test_sessions_response_uses_snake_case_paginated_envelope(): void
    {
        $user = $this->loginAsRegularUser();
        $this->grantManageDocuments($user->getId());
        $document = $this->createDocumentForManager($user->getId());

        $this->getJson('/api/statistics/manage/document/'.$document->uuid.'/engagement/sessions')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
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

    private function grantManageDocuments(int $userId): void
    {
        $permission = Permission::firstOrCreate(['type' => 'manage-documents']);
        UserPermission::firstOrCreate([
            'user_id' => $userId,
            'permission_id' => $permission->getKey(),
        ]);
    }

    private function createDocumentForManager(int $userId): Document
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
            'delay' => 0,
        ]);
    }

    private function fakeUuid(): string
    {
        return '00000000-0000-0000-0000-000000000000';
    }
}

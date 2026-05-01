<?php

namespace Modules\Document\Tests\Feature;

use Illuminate\Support\Str;
use Modules\Access\Models\Permission;
use Modules\Access\Models\UserPermission;
use Modules\Document\Models\Document;
use Modules\User\DTO\UserDTO;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('feature')]
#[Group('Document')]
class DocumentAuthorizationTest extends FeatureTestCase
{
    public function test_unauthenticated_user_is_rejected_with_401(): void
    {
        $response = $this->postJson('/api/documents', []);

        $response->assertStatus(401);
    }

    // ---- Permission gate (no `manage-documents`) ----

    public function test_user_without_manage_documents_permission_cannot_create_document(): void
    {
        $this->loginAsRegularUser();

        $this->postJson('/api/documents', [])
            ->assertStatus(403);
    }

    public function test_user_without_manage_documents_permission_cannot_update_document(): void
    {
        $this->loginAsRegularUser();

        $this->postJson('/api/manage/documents/'.$this->fakeUuid(), [])
            ->assertStatus(403);
    }

    public function test_user_without_manage_documents_permission_cannot_delete_document(): void
    {
        $this->loginAsRegularUser();

        $this->deleteJson('/api/manage/documents/'.$this->fakeUuid().'/delete')
            ->assertStatus(403);
    }

    public function test_user_without_manage_documents_permission_cannot_assign_user(): void
    {
        $this->loginAsRegularUser();

        $this->putJson('/api/manage/documents/'.$this->fakeUuid().'/users/1', [
            'assign' => true,
        ])->assertStatus(403);
    }

    public function test_user_without_manage_documents_permission_cannot_list_users_for_document(): void
    {
        $this->loginAsRegularUser();

        $this->getJson('/api/manage/documents/'.$this->fakeUuid().'/users')
            ->assertStatus(403);
    }

    // ---- Ownership gate (has permission but is not the manager) ----

    public function test_permissioned_user_cannot_update_someone_elses_document(): void
    {
        $owner = $this->makeUser(['name' => 'Owner', 'email' => 'owner@example.com', 'password' => bcrypt('x')]);
        $document = $this->createDocumentForManager($owner->getId());

        $intruder = $this->loginAsRegularUser();
        $this->grantManageDocuments($intruder->getId());

        $this->postJson('/api/manage/documents/'.$document->uuid, [])
            ->assertStatus(403);
    }

    public function test_permissioned_user_cannot_delete_someone_elses_document(): void
    {
        $owner = $this->makeUser(['name' => 'Owner', 'email' => 'owner@example.com', 'password' => bcrypt('x')]);
        $document = $this->createDocumentForManager($owner->getId());

        $intruder = $this->loginAsRegularUser();
        $this->grantManageDocuments($intruder->getId());

        $this->deleteJson('/api/manage/documents/'.$document->uuid.'/delete')
            ->assertStatus(403);
    }

    public function test_permissioned_user_cannot_assign_users_on_someone_elses_document(): void
    {
        $owner = $this->makeUser(['name' => 'Owner', 'email' => 'owner@example.com', 'password' => bcrypt('x')]);
        $document = $this->createDocumentForManager($owner->getId());

        $intruder = $this->loginAsRegularUser();
        $this->grantManageDocuments($intruder->getId());

        $this->putJson('/api/manage/documents/'.$document->uuid.'/users/1', [
            'assign' => true,
        ])->assertStatus(403);
    }

    public function test_permissioned_user_cannot_list_users_for_someone_elses_document(): void
    {
        $owner = $this->makeUser(['name' => 'Owner', 'email' => 'owner@example.com', 'password' => bcrypt('x')]);
        $document = $this->createDocumentForManager($owner->getId());

        $intruder = $this->loginAsRegularUser();
        $this->grantManageDocuments($intruder->getId());

        $this->getJson('/api/manage/documents/'.$document->uuid.'/users')
            ->assertStatus(403);
    }

    // ---- Both gates pass ----

    public function test_create_passes_when_user_has_permission(): void
    {
        // No resource yet — only permission matters.
        $user = $this->loginAsRegularUser();
        $this->grantManageDocuments($user->getId());

        $this->postJson('/api/documents', [])
            ->assertStatus(422); // hits validation, meaning authorize() passed
    }

    public function test_assign_passes_when_user_has_permission_and_owns_document(): void
    {
        $user = $this->loginAsRegularUser();
        $this->grantManageDocuments($user->getId());
        $document = $this->createDocumentForManager($user->getId());

        $this->putJson('/api/manage/documents/'.$document->uuid.'/users/1', [])
            ->assertStatus(422); // missing 'assign' field — authorize() passed
    }

    public function test_get_users_passes_when_user_has_permission_and_owns_document(): void
    {
        $user = $this->loginAsRegularUser();
        $this->grantManageDocuments($user->getId());
        $document = $this->createDocumentForManager($user->getId());

        // 200 or 500 (controller-level), but not 403 — authorize() passed.
        $this->getJson('/api/manage/documents/'.$document->uuid.'/users')
            ->assertStatus(200);
    }

    // ---- Helpers ----

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
            'description' => null,
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

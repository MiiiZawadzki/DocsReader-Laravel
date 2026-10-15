<?php

namespace Modules\Analytics\Tests\Feature;

use Illuminate\Support\Str;
use Modules\Access\Models\Permission;
use Modules\Access\Models\UserPermission;
use Modules\Document\Models\Document;
use Modules\Document\Models\UserDocument;
use Modules\User\DTO\UserDTO;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('feature')]
#[Group('Analytics')]
class StatisticsAuthorizationTest extends FeatureTestCase
{
    private const DOCUMENT_ENDPOINTS = ['stats', 'assigned', 'reads', 'ratio', 'charts', 'read'];

    private const MANAGE_ENDPOINTS = ['stats', 'charts', 'read', 'documents/active', 'documents/total'];

    public function test_assigned_reader_cannot_read_a_documents_statistics(): void
    {
        $owner = $this->makeUser(['name' => 'Owner', 'email' => 'owner@example.com', 'password' => bcrypt('x')]);
        $document = $this->makeDocument($owner->getId());

        $reader = $this->loginAsRegularUser();
        UserDocument::create(['document_id' => $document->id, 'user_id' => $reader->getId(), 'created_by' => $owner->getId()]);

        foreach (self::DOCUMENT_ENDPOINTS as $endpoint) {
            $this->getJson('/api/statistics/manage/document/'.$document->uuid.'/'.$endpoint)->assertStatus(403);
        }
    }

    public function test_another_manager_cannot_read_a_documents_statistics(): void
    {
        $owner = $this->makeUser(['name' => 'Owner', 'email' => 'owner@example.com', 'password' => bcrypt('x')]);
        $document = $this->makeDocument($owner->getId());

        $intruder = $this->loginAsRegularUser();
        $this->grantManageDocuments($intruder->getId());

        foreach (self::DOCUMENT_ENDPOINTS as $endpoint) {
            $this->getJson('/api/statistics/manage/document/'.$document->uuid.'/'.$endpoint)->assertStatus(403);
        }
    }

    public function test_unknown_document_is_forbidden_instead_of_a_server_error(): void
    {
        $user = $this->loginAsRegularUser();
        $this->grantManageDocuments($user->getId());

        $this->getJson('/api/statistics/manage/document/00000000-0000-0000-0000-000000000000/assigned')
            ->assertStatus(403);
    }

    public function test_documents_manager_can_read_its_statistics(): void
    {
        $user = $this->loginAsRegularUser();
        $this->grantManageDocuments($user->getId());
        $document = $this->makeDocument($user->getId());

        foreach (self::DOCUMENT_ENDPOINTS as $endpoint) {
            $this->getJson('/api/statistics/manage/document/'.$document->uuid.'/'.$endpoint)->assertStatus(200);
        }
    }

    public function test_user_without_manage_permission_cannot_reach_manager_endpoints(): void
    {
        $this->loginAsRegularUser();

        foreach (self::MANAGE_ENDPOINTS as $endpoint) {
            $this->getJson('/api/statistics/manage/'.$endpoint)->assertStatus(403);
        }
        $this->getJson('/api/manage/documents')->assertStatus(403);
    }

    public function test_manager_can_reach_manager_endpoints(): void
    {
        $user = $this->loginAsRegularUser();
        $this->grantManageDocuments($user->getId());

        foreach (self::MANAGE_ENDPOINTS as $endpoint) {
            $this->getJson('/api/statistics/manage/'.$endpoint)->assertStatus(200);
        }
        $this->getJson('/api/manage/documents')->assertStatus(200);
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

    private function makeDocument(int $managerId): Document
    {
        return Document::create([
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
        ]);
    }
}

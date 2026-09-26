<?php

namespace Modules\User\Tests\Feature\Console;

use Illuminate\Support\Facades\Hash;
use Modules\Access\Models\Permission;
use Modules\Access\Models\UserPermission;
use Modules\User\Models\User;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('feature')]
#[Group('User')]
class CreateAdminUserTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('access:sync-permissions')->assertSuccessful();
    }

    public function test_it_creates_a_user_with_every_configured_permission(): void
    {
        $this->artisan('user:create-admin')
            ->expectsQuestion('Name', 'Admin User')
            ->expectsQuestion('Email', 'admin@example.com')
            ->expectsQuestion('Password (min. 8 characters, hidden)', 'SecurePassword123!')
            ->expectsQuestion('Confirm password', 'SecurePassword123!')
            ->assertSuccessful();

        $user = User::where('email', 'admin@example.com')->firstOrFail();

        $this->assertSame('Admin User', $user->getAttribute('name'));
        $this->assertTrue(Hash::check('SecurePassword123!', $user->getAttribute('password')));

        foreach ((array) config('permissions') as $permissionKey) {
            $permission = Permission::where('type', $permissionKey)->firstOrFail();

            $this->assertDatabaseHas(UserPermission::class, [
                'user_id' => $user->getKey(),
                'permission_id' => $permission->getKey(),
            ]);
        }
    }

    public function test_it_reasks_for_the_email_when_it_is_already_taken(): void
    {
        $this->makeUser([
            'name' => 'Existing',
            'email' => 'taken@example.com',
            'password' => Hash::make('SecurePassword123!'),
        ]);

        $this->artisan('user:create-admin')
            ->expectsQuestion('Name', 'Admin User')
            ->expectsQuestion('Email', 'taken@example.com')
            ->expectsOutputToContain('has already been taken')
            ->expectsQuestion('Email', 'admin@example.com')
            ->expectsQuestion('Password (min. 8 characters, hidden)', 'SecurePassword123!')
            ->expectsQuestion('Confirm password', 'SecurePassword123!')
            ->assertSuccessful();

        $this->assertDatabaseHas(User::class, ['email' => 'admin@example.com']);
    }

    public function test_it_reasks_for_the_password_when_the_confirmation_does_not_match(): void
    {
        $this->artisan('user:create-admin')
            ->expectsQuestion('Name', 'Admin User')
            ->expectsQuestion('Email', 'admin@example.com')
            ->expectsQuestion('Password (min. 8 characters, hidden)', 'SecurePassword123!')
            ->expectsQuestion('Confirm password', 'Mistyped123!')
            ->expectsOutputToContain('The passwords do not match.')
            ->expectsQuestion('Password (min. 8 characters, hidden)', 'SecurePassword123!')
            ->expectsQuestion('Confirm password', 'SecurePassword123!')
            ->assertSuccessful();

        $this->assertDatabaseCount(User::class, 1);
    }

    public function test_it_rejects_a_password_shorter_than_eight_characters(): void
    {
        $this->artisan('user:create-admin')
            ->expectsQuestion('Name', 'Admin User')
            ->expectsQuestion('Email', 'admin@example.com')
            ->expectsQuestion('Password (min. 8 characters, hidden)', 'short')
            ->expectsOutputToContain('at least 8 characters')
            ->expectsQuestion('Password (min. 8 characters, hidden)', 'SecurePassword123!')
            ->expectsQuestion('Confirm password', 'SecurePassword123!')
            ->assertSuccessful();

        $this->assertDatabaseCount(User::class, 1);
    }
}

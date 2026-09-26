<?php

namespace Modules\Auth\Tests\Feature;

use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('feature')]
#[Group('Auth')]
class AuthThrottleTest extends FeatureTestCase
{
    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        $this->makeUser();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/login', [
                'email' => 'john.doe@example.com',
                'password' => 'WrongPassword',
            ])->assertStatus(401);
        }

        $this->postJson('/api/login', [
            'email' => 'john.doe@example.com',
            'password' => 'WrongPassword',
        ])->assertStatus(429);
    }

    public function test_login_limit_is_keyed_per_email_so_one_victim_does_not_lock_out_others(): void
    {
        $this->makeUser();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/login', [
                'email' => 'john.doe@example.com',
                'password' => 'WrongPassword',
            ])->assertStatus(401);
        }

        // Same IP, different email: still inside the per-IP ceiling of 20.
        $this->postJson('/api/login', [
            'email' => 'someone.else@example.com',
            'password' => 'WrongPassword',
        ])->assertStatus(401);
    }

    public function test_a_successful_login_still_works_within_the_limit(): void
    {
        $this->makeUser();

        $this->postJson('/api/login', [
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
        ])->assertStatus(200);
    }

    public function test_registration_is_rate_limited_per_ip(): void
    {
        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $this->postJson('/api/register', [
                'name' => 'User ' . $attempt,
                'email' => "user{$attempt}@example.com",
                'password' => 'SecurePassword123!',
                'password_confirmation' => 'SecurePassword123!',
            ])->assertStatus(201);
        }

        $this->postJson('/api/register', [
            'name' => 'User 11',
            'email' => 'user11@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ])->assertStatus(429);
    }
}

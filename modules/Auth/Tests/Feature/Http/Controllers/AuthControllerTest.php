<?php

namespace Modules\Auth\Tests\Feature\Http\Controllers;

use Illuminate\Testing\TestResponse;
use Modules\Auth\Http\Controllers\AuthController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\Feature\FeatureTestCase;

#[CoversClass(AuthController::class)]
#[Group('feature')]
#[Group('Auth')]
class AuthControllerTest extends FeatureTestCase
{
    public function test_register_method_with_correct_data_should_create_user(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ];

        $response = $this->postJson('/api/register', $requestData);

        $response->assertStatus(201);
        $response->assertJson(['message' => __('auth::messages.register_success')]);
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);
    }

    public function test_register_method_with_incorrect_data_should_not_create_user(): void
    {
        $requestData = [
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ];

        $response = $this->postJson('/api/register', $requestData);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'The name field is required.']);
        $this->assertDatabaseEmpty('users');
    }

    public function test_login_method_with_correct_data_issues_jwt_cookie(): void
    {
        $this->makeUser();

        $response = $this->postJson('/api/login', [
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'user' => [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'permissions' => [],
            ],
        ]);

        $cookieName = config('auth_cookie.name');
        $response->assertCookie($cookieName);

        $cookie = collect($response->headers->getCookies())
            ->first(fn ($c) => $c->getName() === $cookieName);

        $this->assertNotNull($cookie);
        $this->assertNotEmpty($cookie->getValue());
        $this->assertTrue($cookie->isHttpOnly());
    }

    public function test_login_method_with_incorrect_data_returns_401_and_no_cookie(): void
    {
        $this->makeUser();

        $response = $this->postJson('/api/login', [
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['message' => __('auth::messages.failed')]);

        $cookieName = config('auth_cookie.name');
        $cookie = collect($response->headers->getCookies())
            ->first(fn ($c) => $c->getName() === $cookieName);
        $this->assertNull($cookie);

        $this->assertGuest('jwt');
    }

    public function test_logout_method_clears_jwt_cookie(): void
    {
        $this->makeUser();

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
        ]);

        $cookieName = config('auth_cookie.name');
        $token = $loginResponse->headers->getCookies()[0]->getValue();

        $response = $this
            ->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/logout');

        $response->assertStatus(200);
        $response->assertJson(['message' => __('auth::messages.logout_success')]);

        $cleared = collect($response->headers->getCookies())
            ->first(fn ($c) => $c->getName() === $cookieName);
        $this->assertNotNull($cleared);
        $this->assertEmpty($cleared->getValue());
    }

    public function test_refresh_returns_new_jwt_cookie(): void
    {
        $this->makeUser();

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
        ]);

        $cookieName = config('auth_cookie.name');
        $original = $loginResponse->headers->getCookies()[0]->getValue();

        $response = $this
            ->withHeader('Authorization', "Bearer $original")
            ->postJson('/api/refresh');

        $response->assertStatus(200);

        $refreshed = collect($response->headers->getCookies())
            ->first(fn ($c) => $c->getName() === $cookieName);

        $this->assertNotNull($refreshed);
        $this->assertNotSame($original, $refreshed->getValue());
        $this->assertTrue($refreshed->isHttpOnly());
    }

    public function test_login_cookie_outlives_the_token_until_it_can_no_longer_be_refreshed(): void
    {
        $this->makeUser();

        $cookie = $this->postJson('/api/login', [
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
        ])->headers->getCookies()[0];

        $this->assertEqualsWithDelta(now()->addMinutes(config('jwt.refresh_ttl'))->timestamp, $cookie->getExpiresTime(), 5);
    }

    public function test_expired_token_can_be_refreshed(): void
    {
        $token = $this->loginAndGetToken();
        $this->travel(config('jwt.ttl') + 1)->minutes();
        // The guard remembers the user from the login request; a real request starts without it
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/user')->assertStatus(401);

        $response = $this->withToken($token)->postJson('/api/refresh');

        $response->assertStatus(200);
        $refreshed = $this->authCookie($response)->getValue();
        $this->assertNotSame($token, $refreshed);

        $this->app['auth']->forgetGuards();
        $this->withToken($refreshed)->getJson('/api/user')->assertStatus(200);
    }

    public function test_token_past_the_refresh_window_cannot_be_refreshed(): void
    {
        $token = $this->loginAndGetToken();
        $this->travel(config('jwt.refresh_ttl') + 1)->minutes();

        $response = $this->withToken($token)->postJson('/api/refresh');

        $response->assertStatus(401);
        $response->assertJson(['message' => __('auth::messages.session_expired')]);
        $this->assertEmpty($this->authCookie($response)->getValue());
    }

    public function test_refresh_without_a_token_returns_401(): void
    {
        $this->postJson('/api/refresh')->assertStatus(401);
    }

    public function test_a_token_cannot_be_refreshed_twice(): void
    {
        $token = $this->loginAndGetToken();

        $this->withToken($token)->postJson('/api/refresh')->assertStatus(200);
        // Past the blacklist grace period, which only covers requests already in flight with the old token
        $this->travel(config('jwt.blacklist_grace_period') + 1)->seconds();

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->postJson('/api/refresh')->assertStatus(401);
    }

    public function test_logout_with_an_expired_token_revokes_it(): void
    {
        $token = $this->loginAndGetToken();
        $this->travel(config('jwt.ttl') + 1)->minutes();

        $response = $this->withToken($token)->postJson('/api/logout');

        $response->assertStatus(200);
        $this->assertEmpty($this->authCookie($response)->getValue());

        $this->travel(config('jwt.blacklist_grace_period') + 1)->seconds();
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->postJson('/api/refresh')->assertStatus(401);
    }

    private function loginAndGetToken(): string
    {
        $this->makeUser();

        return $this->postJson('/api/login', [
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
        ])->headers->getCookies()[0]->getValue();
    }

    private function authCookie(TestResponse $response): Cookie
    {
        return collect($response->headers->getCookies())
            ->first(fn ($c) => $c->getName() === config('auth_cookie.name'));
    }
}

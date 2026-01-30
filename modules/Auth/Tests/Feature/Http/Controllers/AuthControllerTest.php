<?php

namespace Modules\Auth\Tests\Feature\Http\Controllers;

use Modules\Auth\Http\Controllers\AuthController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[CoversClass(AuthController::class)]
#[Group('feature')]
#[Group('Auth')]
class AuthControllerTest extends FeatureTestCase
{
    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function test_register_method_with_incorrect_data_should_not_create_user(): void
    {
        $requestData = [
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ];

        $response = $this->postJson('/api/register', $requestData);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => "The name field is required."]);
        $this->assertDatabaseEmpty('users');
    }

    /**
     * @return void
     */
    public function test_login_method_with_correct_data_should_login_user(): void
    {
        $this->makeUser();

        $requestData = [
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
        ];

        $response = $this->postJson('/api/login', $requestData);
        $response->assertStatus(200);
        $response->assertJson([
            'user' => [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'permissions' => [],
            ]
        ]);
        $this->assertAuthenticated('web');
    }

    /**
     * @return void
     */
    public function test_login_method_with_incorrect_data_should_not_login_user(): void
    {
        $this->makeUser();

        $requestData = [
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword',
        ];

        $response = $this->postJson('/api/login', $requestData);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => __('auth::messages.failed')
        ]);

        $this->assertGuest('web');
    }

    /**
     * @return void
     */
    public function test_logout_method_should_logout_user(): void
    {
        $this->makeUser();

        $requestData = [
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
        ];

        $this->postJson('/api/login', $requestData);
        $this->assertAuthenticated('web');

        $response = $this->postJson('/api/logout');

        $this->assertGuest('web');
        $response->assertStatus(200);
        $response->assertJson([
            'message' => __('auth::messages.logout_success')
        ]);
    }
}

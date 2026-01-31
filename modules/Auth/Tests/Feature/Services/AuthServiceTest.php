<?php

namespace Modules\Auth\Tests\Feature\Services;

use Modules\Auth\DTO\LoginUserDTO;
use Modules\Auth\DTO\RegisterDataDTO;
use Modules\Auth\Services\AuthService;
use Modules\User\DTO\UserDTO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[CoversClass(AuthService::class)]
#[Group('feature')]
#[Group('Auth')]
class AuthServiceTest extends FeatureTestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService = app()->make(AuthService::class);
    }

    /**
     * @return void
     */
    public function test_register_method_should_create_user(): void
    {
        $userData = [
            'name' => fake()->name,
            'email' => fake()->email,
            'password' => fake()->password
        ];

        $userDataDto = new RegisterDataDTO($userData);

        $response = $this->authService->register($userDataDto);

        $this->assertInstanceOf(UserDTO::class, $response);
        $this->assertSame($userData['name'], $response->name);
        $this->assertSame($userData['email'], $response->email);

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);
    }

    /**
     * @return void
     */
    public function test_login_method_should_login_user(): void
    {
        $user = $this->makeUser();

        $this->assertGuest();

        $loginData = new LoginUserDTO([
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
        ]);

        $response = $this->authService->login($loginData);

        $this->assertInstanceOf(UserDTO::class, $response);
        $this->assertSame($user->name, $response->name);
        $this->assertSame($user->email, $response->email);

        $this->assertAuthenticated();
    }

    /**
     * @return void
     */
    public function test_login_method_should_return_null_when_user_not_exist(): void
    {
        $this->assertGuest();

        $loginData = new LoginUserDTO([
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
        ]);

        $response = $this->authService->login($loginData);

        $this->assertNull($response);
        $this->assertGuest();
    }

    /**
     * @return void
     */
    public function test_login_method_should_return_null_when_wrong_email(): void
    {
        $this->makeUser();

        $this->assertGuest();

        $loginData = new LoginUserDTO([
            'email' => 'john.doe@example.comm',
            'password' => 'SecurePassword123!',
        ]);

        $response = $this->authService->login($loginData);

        $this->assertNull($response);
        $this->assertGuest();
    }

    /**
     * @return void
     */
    public function test_login_method_should_return_null_when_wrong_password(): void
    {
        $this->makeUser();

        $this->assertGuest();

        $loginData = new LoginUserDTO([
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123',
        ]);

        $response = $this->authService->login($loginData);

        $this->assertNull($response);
        $this->assertGuest();
    }

    /**
     * @return void
     */
    public function test_logout_method_should_logout_current_user(): void
    {
        $this->makeUser();

        $this->assertGuest();

        $loginData = new LoginUserDTO([
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
        ]);

        $this->authService->login($loginData);

        $this->assertAuthenticated();

        $this->authService->logout();

        $this->assertGuest();
    }
}

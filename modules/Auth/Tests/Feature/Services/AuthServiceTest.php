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

    public function test_register_method_should_create_user(): void
    {
        $userData = [
            'name' => fake()->name,
            'email' => fake()->email,
            'password' => fake()->password,
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

    public function test_login_method_returns_token_and_user_dto(): void
    {
        $user = $this->makeUser();

        $loginData = new LoginUserDTO([
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
        ]);

        $result = $this->authService->login($loginData);

        $this->assertIsArray($result);
        [$token, $userDto] = $result;

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertInstanceOf(UserDTO::class, $userDto);
        $this->assertSame($user->name, $userDto->name);
        $this->assertSame($user->email, $userDto->email);

        $this->assertAuthenticated('jwt');
    }

    public function test_login_method_should_return_null_when_user_not_exist(): void
    {
        $this->assertGuest('jwt');

        $loginData = new LoginUserDTO([
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
        ]);

        $response = $this->authService->login($loginData);

        $this->assertNull($response);
        $this->assertGuest('jwt');
    }

    public function test_login_method_should_return_null_when_wrong_email(): void
    {
        $this->makeUser();

        $this->assertGuest('jwt');

        $loginData = new LoginUserDTO([
            'email' => 'john.doe@example.comm',
            'password' => 'SecurePassword123!',
        ]);

        $response = $this->authService->login($loginData);

        $this->assertNull($response);
        $this->assertGuest('jwt');
    }

    public function test_login_method_should_return_null_when_wrong_password(): void
    {
        $this->makeUser();

        $this->assertGuest('jwt');

        $loginData = new LoginUserDTO([
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123',
        ]);

        $response = $this->authService->login($loginData);

        $this->assertNull($response);
        $this->assertGuest('jwt');
    }
}

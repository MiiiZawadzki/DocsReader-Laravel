<?php

namespace Modules\Auth\Tests\Unit\Services;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Hashing\Hasher;
use Modules\Auth\DTO\LoginUserDTO;
use Modules\Auth\DTO\RegisterDataDTO;
use Modules\Auth\Services\AuthService;
use Modules\User\Api\UserApiInterface;
use Modules\User\DTO\UserDTO;
use Modules\User\Models\User;
use Tests\Unit\UnitTestCase;

class AuthServiceTest extends UnitTestCase
{
    public function test_register_hashes_password_and_creates_user(): void
    {
        $userApi = $this->createMock(UserApiInterface::class);
        $hasher = $this->createMock(Hasher::class);
        $auth = $this->createMock(AuthFactory::class);
        $expectedUserDto = $this->createMock(UserDTO::class);

        $hashedPassword = 'hashed-password';
        $plainPassword = 'plain-password';
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => $plainPassword,
        ];

        $hasher->expects($this->once())
            ->method('make')
            ->with($plainPassword)
            ->willReturn($hashedPassword);

        $authService = new AuthService($userApi, $hasher, $auth);
        $registerDto = new RegisterDataDTO($userData);

        $userApi
            ->expects($this->once())
            ->method('createUser')
            ->with($this->callback(function (array $dataArray) use ($registerDto, $hashedPassword) {

                $this->assertSame($registerDto->name, $dataArray['name']);
                $this->assertSame($registerDto->email, $dataArray['email']);
                $this->assertSame($hashedPassword, $dataArray['password']);

                return true;
            }))
            ->willReturn($expectedUserDto);

        $result = $authService->register($registerDto);

        $this->assertSame($expectedUserDto, $result);
    }

    public function test_login_returns_user_when_credentials_are_valid(): void
    {
        $userApi = $this->createMock(UserApiInterface::class);
        $hasher = $this->createMock(Hasher::class);
        $auth = $this->createMock(AuthFactory::class);
        $guard = $this->createMock(StatefulGuard::class);
        $authService = new AuthService($userApi, $hasher, $auth);

        $credentialsData = [
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ];

        $loginDto = new LoginUserDTO($credentialsData);
        $userMock = $this->createMock(User::class);

        $userMock->expects($this->once())
            ->method('getKey')
            ->willReturn(1);

        $userMock->expects($this->exactly(2))
            ->method('getAttribute')
            ->willReturnCallback(function ($attribute) {
                return match ($attribute) {
                    'name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                };
            });

        $auth->expects($this->once())
            ->method('guard')
            ->willReturn($guard);

        $guard->expects($this->once())
            ->method('attempt')
            ->with($credentialsData)
            ->willReturn(true);

        $guard->expects($this->once())
            ->method('user')
            ->willReturn($userMock);

        $result = $authService->login($loginDto);

        $this->assertInstanceOf(UserDTO::class, $result);
        $this->assertSame(1, $result->getId());
        $this->assertSame('John Doe', $result->getName());
        $this->assertSame('john.doe@example.com', $result->getEmail());
    }

    public function test_login_returns_null_when_credentials_are_invalid(): void
    {
        $userApi = $this->createMock(UserApiInterface::class);
        $hasher = $this->createMock(Hasher::class);
        $auth = $this->createMock(AuthFactory::class);
        $guard = $this->createMock(StatefulGuard::class);
        $authService = new AuthService($userApi, $hasher, $auth);

        $credentialsData = [
            'email' => 'john.doe@example.com',
            'password' => 'wrong-password',
        ];

        $loginDto = new LoginUserDTO($credentialsData);

        $auth->expects($this->once())
            ->method('guard')
            ->willReturn($guard);

        $guard->expects($this->once())
            ->method('attempt')
            ->with($credentialsData)
            ->willReturn(false);

        $guard->expects($this->never())
            ->method('user');

        $result = $authService->login($loginDto);

        $this->assertNull($result);
    }

    public function test_logs_out_user(): void
    {
        $userApi = $this->createMock(UserApiInterface::class);
        $hasher = $this->createMock(Hasher::class);
        $auth = $this->createMock(AuthFactory::class);
        $authService = new AuthService($userApi, $hasher, $auth);

        $user = $this->createMock(User::class);
        $guard = $this->createMock(StatefulGuard::class);

        $auth->expects($this->once())
            ->method('guard')
            ->with('web')
            ->willReturn($guard);

        $guard->expects($this->once())
            ->method('logout');

        $authService->logout($user);
    }
}

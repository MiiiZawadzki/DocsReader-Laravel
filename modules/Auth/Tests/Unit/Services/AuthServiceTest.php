<?php

namespace Modules\Auth\Tests\Unit\Services;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Hashing\Hasher;
use Modules\Auth\DTO\LoginUserDTO;
use Modules\Auth\DTO\RegisterDataDTO;
use Modules\Auth\Services\AuthService;
use Modules\User\Api\UserApiInterface;
use Modules\User\DTO\UserDTO;
use Modules\User\Models\User;
use Tests\Unit\UnitTestCase;
use Tymon\JWTAuth\JWT;
use Tymon\JWTAuth\JWTGuard;
use Tymon\JWTAuth\Manager;
use Tymon\JWTAuth\Token;

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

        $authService = new AuthService($userApi, $hasher, $auth, $this->createMock(JWT::class));
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

    public function test_login_returns_token_and_user_when_credentials_are_valid(): void
    {
        $userApi = $this->createMock(UserApiInterface::class);
        $hasher = $this->createMock(Hasher::class);
        $auth = $this->createMock(AuthFactory::class);
        $guard = $this->createMock(JWTGuard::class);
        $authService = new AuthService($userApi, $hasher, $auth, $this->createMock(JWT::class));

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

        $auth->expects($this->exactly(2))
            ->method('guard')
            ->with('jwt')
            ->willReturn($guard);

        $guard->expects($this->once())
            ->method('attempt')
            ->with($credentialsData)
            ->willReturn('jwt.token.value');

        $guard->expects($this->once())
            ->method('user')
            ->willReturn($userMock);

        $result = $authService->login($loginDto);

        $this->assertIsArray($result);
        [$token, $userDto] = $result;

        $this->assertSame('jwt.token.value', $token);
        $this->assertInstanceOf(UserDTO::class, $userDto);
        $this->assertSame(1, $userDto->getId());
        $this->assertSame('John Doe', $userDto->getName());
        $this->assertSame('john.doe@example.com', $userDto->getEmail());
    }

    public function test_login_returns_null_when_credentials_are_invalid(): void
    {
        $userApi = $this->createMock(UserApiInterface::class);
        $hasher = $this->createMock(Hasher::class);
        $auth = $this->createMock(AuthFactory::class);
        $guard = $this->createMock(JWTGuard::class);
        $authService = new AuthService($userApi, $hasher, $auth, $this->createMock(JWT::class));

        $credentialsData = [
            'email' => 'john.doe@example.com',
            'password' => 'wrong-password',
        ];

        $loginDto = new LoginUserDTO($credentialsData);

        $auth->expects($this->once())
            ->method('guard')
            ->with('jwt')
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

    public function test_logout_revokes_the_token_in_the_refresh_flow(): void
    {
        $jwt = $this->createMock(JWT::class);
        $manager = $this->createMock(Manager::class);
        $token = new Token('header.payload.signature');
        $authService = new AuthService(
            $this->createMock(UserApiInterface::class),
            $this->createMock(Hasher::class),
            $this->createMock(AuthFactory::class),
            $jwt,
        );

        $jwt->method('getToken')->willReturn($token);
        $jwt->method('manager')->willReturn($manager);

        // The refresh flow lets an expired, still refreshable token be revoked as well
        $manager->expects($this->once())->method('setRefreshFlow')->willReturnSelf();
        $manager->expects($this->once())->method('invalidate')->with($token);

        $authService->logout();
    }

    public function test_logout_without_a_token_does_nothing(): void
    {
        $jwt = $this->createMock(JWT::class);
        $authService = new AuthService(
            $this->createMock(UserApiInterface::class),
            $this->createMock(Hasher::class),
            $this->createMock(AuthFactory::class),
            $jwt,
        );

        $jwt->method('getToken')->willReturn(null);
        $jwt->expects($this->never())->method('manager');

        $authService->logout();
    }

    public function test_refresh_returns_new_token_from_jwt_guard(): void
    {
        $userApi = $this->createMock(UserApiInterface::class);
        $hasher = $this->createMock(Hasher::class);
        $auth = $this->createMock(AuthFactory::class);
        $authService = new AuthService($userApi, $hasher, $auth, $this->createMock(JWT::class));

        $guard = $this->createMock(JWTGuard::class);

        $auth->expects($this->once())
            ->method('guard')
            ->with('jwt')
            ->willReturn($guard);

        $guard->expects($this->once())
            ->method('refresh')
            ->willReturn('refreshed.jwt.value');

        $this->assertSame('refreshed.jwt.value', $authService->refresh());
    }
}

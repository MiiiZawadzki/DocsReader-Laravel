<?php

namespace Modules\Auth\Tests\Unit\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\DTO\LoginUserDTO;
use Modules\Auth\DTO\RegisterDataDTO;
use Modules\Auth\Services\AuthService;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    public function test_register_hashes_password_and_creates_user(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $expectedUser = new User();

        $hashedPassword = 'hashed-password';
        $plainPassword = 'plain-password';
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => $plainPassword,
        ];

        Hash::shouldReceive('make')
            ->once()
            ->with($plainPassword)
            ->andReturn($hashedPassword);

        $authService = new AuthService($userRepository);
        $registerDto = new RegisterDataDTO($userData);

        $userRepository
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (array $dataArray) use ($registerDto, $hashedPassword) {

                $this->assertSame($registerDto->name, $dataArray['name']);
                $this->assertSame($registerDto->email, $dataArray['email']);
                $this->assertSame($hashedPassword, $dataArray['password']);

                return true;
            }))
            ->willReturn($expectedUser);

        $result = $authService->register($registerDto);

        $this->assertSame($expectedUser, $result);
    }

    public function test_login_returns_user_when_credentials_are_valid(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $authService = new AuthService($userRepository);

        $credentialsData = [
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ];

        $loginDto = new LoginUserDTO($credentialsData);
        $expectedUser = new User();

        Auth::shouldReceive('attempt')
            ->once()
            ->with($credentialsData)
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($expectedUser);

        $result = $authService->login($loginDto);

        $this->assertSame($expectedUser, $result);
    }

    public function test_login_returns_null_when_credentials_are_invalid(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $authService = new AuthService($userRepository);

        $credentialsData = [
            'email' => 'john.doe@example.com',
            'password' => 'wrong-password',
        ];

        $loginDto = new LoginUserDTO($credentialsData);

        Auth::shouldReceive('attempt')
            ->once()
            ->with($credentialsData)
            ->andReturn(false);

        Auth::shouldReceive('user')
            ->never();

        $result = $authService->login($loginDto);

        $this->assertNull($result);
    }

    public function test_logout_deletes_tokens_and_logs_out_user(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $authService = new AuthService($userRepository);

        $user = $this->createMock(User::class);
        $tokens = \Mockery::mock();
        $guard = \Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);

        $user->expects($this->once())
            ->method('tokens')
            ->willReturn($tokens);

        $tokens->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        Auth::shouldReceive('guard')
            ->once()
            ->with('web')
            ->andReturn($guard);

        $guard->shouldReceive('logout')
            ->once();

        $authService->logout($user);
    }
}

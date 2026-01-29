<?php

namespace Modules\Auth\Tests\Unit\Http\Controllers\Api;

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Translation\Translator;
use Modules\Access\Api\AccessApiInterface;
use Modules\Auth\DTO\LoginUserDTO;
use Modules\Auth\DTO\RegisterDataDTO;
use Modules\Auth\Http\Controllers\AuthController;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Services\AuthService;
use Modules\User\Api\UserApiInterface;
use Modules\User\DTO\UserDTO;
use Modules\User\Models\User;
use Tests\Unit\UnitTestCase;

class AuthControllerTest extends UnitTestCase
{
    public function test_register_calls_service_with_register_data_dto(): void
    {
        $authService = $this->createMock(AuthService::class);
        $sessionMock = $this->createMock(Session::class);
        $userApiMock = $this->createMock(UserApiInterface::class);
        $accessApiMock = $this->createMock(AccessApiInterface::class);
        $registerRequest = $this->createMock(RegisterRequest::class);
        $translatorMock = $this->createMock(Translator::class);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ];

        $registerRequest
            ->expects($this->once())
            ->method('validated')
            ->willReturn($userData);

        $registerRequest
            ->expects($this->once())
            ->method('session')
            ->willReturn($sessionMock);

        $sessionMock
            ->expects($this->once())
            ->method('regenerate');

        $translatorMock
            ->expects($this->once())
            ->method('get')
            ->with('auth::messages.register_success');

        $authService
            ->expects($this->once())
            ->method('register')
            ->with($this->callback(function (RegisterDataDTO $dto) use ($userData) {
                return $dto->name === $userData['name']
                    && $dto->email === $userData['email']
                    && $dto->plainTextPassword === $userData['password'];
            }));

        $controller = new AuthController($authService, $translatorMock, $userApiMock, $accessApiMock);

        $controller->register($registerRequest);
    }


    public function test_login_calls_service_with_login_user_dto_when_credentials_are_valid(): void
    {
        $authService = $this->createMock(AuthService::class);
        $sessionMock = $this->createMock(Session::class);
        $userApiMock = $this->createMock(UserApiInterface::class);
        $accessApiMock = $this->createMock(AccessApiInterface::class);
        $loginRequest = $this->createMock(LoginRequest::class);
        $userMock = $this->createMock(User::class);
        $translatorMock = $this->createMock(Translator::class);

        $credentials = [
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ];

        $loginRequest
            ->expects($this->once())
            ->method('validated')
            ->willReturn($credentials);

        $loginRequest
            ->expects($this->once())
            ->method('session')
            ->willReturn($sessionMock);

        $sessionMock
            ->expects($this->once())
            ->method('regenerate');

        $userMock
            ->expects($this->once())
            ->method('getKey')
            ->willReturn(1);

        $userMock
            ->expects($this->exactly(2))
            ->method('getAttribute')
            ->willReturnCallback(function ($attribute) {
                return match ($attribute) {
                    'name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                };
            });

        $userDto = new UserDTO($userMock);

        $authService
            ->expects($this->once())
            ->method('login')
            ->with($this->callback(function (LoginUserDTO $dto) use ($credentials) {
                return $dto->email === $credentials['email']
                    && $dto->password === $credentials['password'];
            }))
            ->willReturn($userDto);

        $controller = new AuthController($authService, $translatorMock, $userApiMock, $accessApiMock);
        $response = $controller->login($loginRequest);

        $this->assertEquals(200, $response->getStatusCode());
        $userDataFromResponse = $response->getData(true)['user'];

        $this->assertSame($credentials['email'], $userDataFromResponse['email']);
        $this->assertSame('John Doe', $userDataFromResponse['name']);
        $this->assertEmpty($userDataFromResponse['permissions']);
    }

    public function test_login_calls_service_with_login_user_dto_when_credentials_are_invalid(): void
    {
        $authService = $this->createMock(AuthService::class);
        $userApiMock = $this->createMock(UserApiInterface::class);
        $accessApiMock = $this->createMock(AccessApiInterface::class);
        $loginRequest = $this->createMock(LoginRequest::class);
        $translatorMock = $this->createMock(Translator::class);

        $credentials = [
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ];

        $loginRequest
            ->expects($this->once())
            ->method('validated')
            ->willReturn($credentials);

        $translatorMock
            ->expects($this->once())
            ->method('get')
            ->with('auth::messages.failed')
            ->willReturn('auth failed');

        $authService
            ->expects($this->once())
            ->method('login')
            ->with($this->callback(function (LoginUserDTO $dto) use ($credentials) {
                return $dto->email === $credentials['email']
                    && $dto->password === $credentials['password'];
            }))
            ->willReturn(null);

        $controller = new AuthController($authService, $translatorMock, $userApiMock, $accessApiMock);
        $response = $controller->login($loginRequest);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertSame('auth failed', $response->getOriginalContent()['message']);
    }

    public function test_logout_calls_service_with_user(): void
    {
        $authService = $this->createMock(AuthService::class);
        $sessionMock = $this->createMock(Session::class);
        $userApiMock = $this->createMock(UserApiInterface::class);
        $accessApiMock = $this->createMock(AccessApiInterface::class);
        $request = $this->createMock(Request::class);
        $translatorMock = $this->createMock(Translator::class);

        $request
            ->expects($this->exactly(2))
            ->method('session')
            ->willReturn($sessionMock);

        $sessionMock
            ->expects($this->once())
            ->method('invalidate');

        $sessionMock
            ->expects($this->once())
            ->method('regenerateToken');

        $translatorMock
            ->expects($this->once())
            ->method('get')
            ->with('auth::messages.logout_success')
            ->willReturn('auth logout success');

        $authService
            ->expects($this->once())
            ->method('logout');

        $controller = new AuthController($authService, $translatorMock, $userApiMock, $accessApiMock);
        $response = $controller->logout($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('auth logout success', $response->getOriginalContent()['message']);
    }
}

<?php

namespace Modules\Auth\Tests\Unit\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Translation\Translator;
use Mockery;
use Modules\Auth\DTO\LoginUserDTO;
use Modules\Auth\DTO\RegisterDataDTO;
use Modules\Auth\Http\Controllers\AuthController;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Services\AuthService;
use Tests\TestCase;


class AuthControllerTest extends TestCase
{
    public function test_register_calls_service_with_register_data_dto(): void
    {
        $authService = Mockery::mock(AuthService::class);
        $sessionMock = Mockery::mock(Session::class);
        $registerRequest = Mockery::mock(RegisterRequest::class);
        $translatorMock = Mockery::mock(Translator::class);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ];

        $registerRequest->shouldReceive('validated')
            ->andReturn($userData);

        $registerRequest->shouldReceive('session')
            ->andReturn($sessionMock);

        $sessionMock->shouldReceive('regenerate');

        $translatorMock->shouldReceive('get')
            ->withArgs(['auth::messages.register_success']);

        $authService->shouldReceive('register')
            ->once()
            ->with(Mockery::on(function (RegisterDataDTO $dto) use ($userData) {
                return $dto->name === $userData['name']
                    && $dto->email === $userData['email']
                    && $dto->plainTextPassword === $userData['password'];
            }));

        $controller = new AuthController($authService, $translatorMock);
        $controller->register($registerRequest);
    }

    public function test_login_calls_service_with_login_user_dto_when_credentials_are_valid(): void
    {
        $authService = Mockery::mock(AuthService::class);
        $loginRequest = Mockery::mock(LoginRequest::class);
        $user = Mockery::mock(User::class);
        $translatorMock = Mockery::mock(Translator::class);
        $sessionMock = Mockery::mock(Session::class);

        $credentials = [
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ];

        $loginRequest->shouldReceive('session')
            ->andReturn($sessionMock);

        $user->shouldReceive('getAttribute')
            ->withArgs(['name'])
            ->andReturn('John Doe');

        $user->shouldReceive('getAttribute')
            ->withArgs(['email'])
            ->andReturn('john.doe@example.com');


        $user->shouldReceive('getAttribute')
            ->withArgs(['userPermissions'])
            ->andReturn(collect());

        $sessionMock->shouldReceive('regenerate');

        $loginRequest->shouldReceive('validated')
            ->once()
            ->andReturn($credentials);

        $authService->shouldReceive('login')
            ->once()
            ->with(Mockery::on(function (LoginUserDTO $dto) use ($credentials) {
                return $dto->email === $credentials['email']
                    && $dto->password === $credentials['password'];
            }))
            ->andReturn($user);

        $controller = new AuthController($authService, $translatorMock);
        $response = $controller->login($loginRequest);

        $this->assertEquals(200, $response->getStatusCode());
        $userDataFromResponse = $response->getData(true)['user'];

        $this->assertSame($credentials['email'], $userDataFromResponse['email']);
        $this->assertSame('John Doe', $userDataFromResponse['name']);
        $this->assertEmpty($userDataFromResponse['permissions']);
    }

    public function test_login_calls_service_with_login_user_dto_when_credentials_are_invalid(): void
    {
        $authService = Mockery::mock(AuthService::class);
        $loginRequest = Mockery::mock(LoginRequest::class);
        $user = Mockery::mock(User::class);
        $translatorMock = Mockery::mock(Translator::class);
        $sessionMock = Mockery::mock(Session::class);

        $credentials = [
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ];

        $loginRequest->shouldReceive('session')
            ->andReturn($sessionMock);

        $user->shouldReceive('getAttribute')
            ->withArgs(['name'])
            ->andReturn('John Doe');

        $user->shouldReceive('getAttribute')
            ->withArgs(['email'])
            ->andReturn('john.doe@example.com');

        $translatorMock->shouldReceive('get')
            ->withArgs(['auth::messages.failed'])
            ->andReturn('auth failed')
            ->once();

        $user->shouldReceive('getAttribute')
            ->withArgs(['userPermissions'])
            ->andReturn(collect());

        $sessionMock->shouldReceive('regenerate');

        $loginRequest->shouldReceive('validated')
            ->once()
            ->andReturn($credentials);

        $authService->shouldReceive('login')
            ->once()
            ->with(Mockery::on(function (LoginUserDTO $dto) use ($credentials) {
                return $dto->email === $credentials['email']
                    && $dto->password === $credentials['password'];
            }))
            ->andReturn(null);

        $controller = new AuthController($authService, $translatorMock);
        $response = $controller->login($loginRequest);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertSame('auth failed', $response->getOriginalContent()['message']);
    }

    public function test_logout_calls_service_with_user(): void
    {
        $authService = Mockery::mock(AuthService::class);
        $request = Mockery::mock(Request::class);
        $user = Mockery::mock(User::class);
        $translatorMock = Mockery::mock(Translator::class);
        $sessionMock = Mockery::mock(Session::class);

        $request->shouldReceive('session')
            ->andReturn($sessionMock);

        $request->shouldReceive('user')
            ->once()
            ->andReturn($user);

        $translatorMock->shouldReceive('get')
            ->withArgs(['auth::messages.logout_success'])
            ->andReturn('auth logout success')
            ->once();

        $sessionMock->shouldReceive('invalidate')->once();
        $sessionMock->shouldReceive('regenerateToken')->once();


        $authService->shouldReceive('logout')
            ->once()
            ->with($user);

        $controller = new AuthController($authService, $translatorMock);
        $response = $controller->logout($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('auth logout success', $response->getOriginalContent()['message']);
    }
}

<?php

namespace Modules\Auth\Tests\Unit\Http\Controllers\Api;

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Container\Container;
use Illuminate\Cookie\CookieJar;
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
use Symfony\Component\HttpFoundation\Cookie;
use Tests\Unit\UnitTestCase;

class AuthControllerTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // AuthController calls config() / config()->set(); wire a minimal
        // container so those helpers resolve without booting the framework.
        $container = new Container();
        $container->instance('config', new ConfigRepository([
            'auth_cookie' => [
                'name' => 'jwt_token',
                'path' => '/',
                'domain' => null,
                'secure' => true,
                'same_site' => 'lax',
            ],
            'jwt' => ['ttl' => 60],
        ]));
        Container::setInstance($container);
    }

    protected function tearDown(): void
    {
        Container::setInstance(null);
        parent::tearDown();
    }

    public function test_register_calls_service_with_register_data_dto(): void
    {
        $authService = $this->createMock(AuthService::class);
        $userApiMock = $this->createMock(UserApiInterface::class);
        $accessApiMock = $this->createMock(AccessApiInterface::class);
        $registerRequest = $this->createMock(RegisterRequest::class);
        $translatorMock = $this->createMock(Translator::class);
        $cookieJar = $this->createMock(CookieJar::class);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ];

        $registerRequest
            ->expects($this->once())
            ->method('validated')
            ->willReturn($userData);

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

        $controller = new AuthController(
            $authService,
            $translatorMock,
            $userApiMock,
            $accessApiMock,
            $cookieJar,
        );

        $controller->register($registerRequest);
    }


    public function test_login_returns_user_payload_and_attaches_jwt_cookie(): void
    {
        $authService = $this->createMock(AuthService::class);
        $userApiMock = $this->createMock(UserApiInterface::class);
        $accessApiMock = $this->createMock(AccessApiInterface::class);
        $loginRequest = $this->createMock(LoginRequest::class);
        $userMock = $this->createMock(User::class);
        $translatorMock = $this->createMock(Translator::class);
        $cookieJar = $this->createMock(CookieJar::class);

        $credentials = [
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ];

        $loginRequest
            ->expects($this->once())
            ->method('validated')
            ->willReturn($credentials);

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
            ->willReturn(['jwt.token.value', $userDto]);

        $cookie = new Cookie('jwt_token', 'jwt.token.value', 0, '/', null, true, true, false, 'lax');
        $cookieJar
            ->expects($this->once())
            ->method('make')
            ->with(
                'jwt_token',
                'jwt.token.value',
                60,
                '/',
                null,
                true,
                true,
                false,
                'lax',
            )
            ->willReturn($cookie);

        $controller = new AuthController(
            $authService,
            $translatorMock,
            $userApiMock,
            $accessApiMock,
            $cookieJar,
        );
        $response = $controller->login($loginRequest);

        $this->assertEquals(200, $response->getStatusCode());
        $userDataFromResponse = $response->getData(true)['user'];

        $this->assertSame($credentials['email'], $userDataFromResponse['email']);
        $this->assertSame('John Doe', $userDataFromResponse['name']);
        $this->assertEmpty($userDataFromResponse['permissions']);

        $cookies = $response->headers->getCookies();
        $this->assertCount(1, $cookies);
        $this->assertSame('jwt_token', $cookies[0]->getName());
        $this->assertSame('jwt.token.value', $cookies[0]->getValue());
        $this->assertTrue($cookies[0]->isHttpOnly());
    }

    public function test_login_returns_401_and_no_cookie_when_credentials_are_invalid(): void
    {
        $authService = $this->createMock(AuthService::class);
        $userApiMock = $this->createMock(UserApiInterface::class);
        $accessApiMock = $this->createMock(AccessApiInterface::class);
        $loginRequest = $this->createMock(LoginRequest::class);
        $translatorMock = $this->createMock(Translator::class);
        $cookieJar = $this->createMock(CookieJar::class);

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

        $cookieJar->expects($this->never())->method('make');

        $controller = new AuthController(
            $authService,
            $translatorMock,
            $userApiMock,
            $accessApiMock,
            $cookieJar,
        );
        $response = $controller->login($loginRequest);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertSame('auth failed', $response->getOriginalContent()['message']);
        $this->assertEmpty($response->headers->getCookies());
    }

    public function test_logout_invalidates_token_and_forgets_cookie(): void
    {
        $authService = $this->createMock(AuthService::class);
        $userApiMock = $this->createMock(UserApiInterface::class);
        $accessApiMock = $this->createMock(AccessApiInterface::class);
        $translatorMock = $this->createMock(Translator::class);
        $cookieJar = $this->createMock(CookieJar::class);

        $translatorMock
            ->expects($this->once())
            ->method('get')
            ->with('auth::messages.logout_success')
            ->willReturn('auth logout success');

        $authService->expects($this->once())->method('logout');

        $forgotten = new Cookie('jwt_token', '', 1, '/', null);
        $cookieJar
            ->expects($this->once())
            ->method('forget')
            ->with('jwt_token', '/', null)
            ->willReturn($forgotten);

        $controller = new AuthController(
            $authService,
            $translatorMock,
            $userApiMock,
            $accessApiMock,
            $cookieJar,
        );
        $response = $controller->logout();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('auth logout success', $response->getOriginalContent()['message']);
        $this->assertCount(1, $response->headers->getCookies());
    }

    public function test_refresh_swaps_cookie_for_a_new_token(): void
    {
        $authService = $this->createMock(AuthService::class);
        $userApiMock = $this->createMock(UserApiInterface::class);
        $accessApiMock = $this->createMock(AccessApiInterface::class);
        $translatorMock = $this->createMock(Translator::class);
        $cookieJar = $this->createMock(CookieJar::class);

        $authService
            ->expects($this->once())
            ->method('refresh')
            ->willReturn('refreshed.jwt');

        $cookie = new Cookie('jwt_token', 'refreshed.jwt', 0, '/', null, true, true, false, 'lax');
        $cookieJar
            ->expects($this->once())
            ->method('make')
            ->willReturn($cookie);

        $controller = new AuthController(
            $authService,
            $translatorMock,
            $userApiMock,
            $accessApiMock,
            $cookieJar,
        );
        $response = $controller->refresh();

        $this->assertEquals(200, $response->getStatusCode());
        $cookies = $response->headers->getCookies();
        $this->assertCount(1, $cookies);
        $this->assertSame('refreshed.jwt', $cookies[0]->getValue());
    }
}

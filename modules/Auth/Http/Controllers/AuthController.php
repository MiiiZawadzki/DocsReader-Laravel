<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Cookie\CookieJar;
use Illuminate\Http\JsonResponse;
use Illuminate\Translation\Translator;
use Modules\Access\Api\AccessApiInterface;
use Modules\Auth\DTO\LoginResponseDTO;
use Modules\Auth\DTO\LoginUserDTO;
use Modules\Auth\DTO\RegisterDataDTO;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Services\AuthService;
use Modules\User\Api\UserApiInterface;

class AuthController
{
    public function __construct(
        public readonly AuthService $service,
        public readonly Translator $translator,
        public readonly UserApiInterface $userApi,
        public readonly AccessApiInterface $accessApi,
        public readonly CookieJar $cookieJar,
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $userData = $request->validated();

        $this->service->register(
            new RegisterDataDTO([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
            ])
        );

        return new JsonResponse(
            ['message' => $this->translator->get('auth::messages.register_success')],
            201
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $result = $this->service->login(
            new LoginUserDTO([
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ])
        );

        if ($result === null) {
            return new JsonResponse(
                ['message' => $this->translator->get('auth::messages.failed')],
                401
            );
        }

        [$token, $userDto] = $result;

        $response = new JsonResponse(
            [
                'user' => new LoginResponseDTO(
                    $userDto->getId(),
                    $userDto->getName(),
                    $userDto->getEmail(),
                    $this->accessApi->getPermissionsForUser($userDto->getId()),
                ),
            ],
            200
        );

        return $response->withCookie($this->makeAuthCookie($token));
    }

    public function refresh(): JsonResponse
    {
        $token = $this->service->refresh();

        return (new JsonResponse(['message' => 'refreshed'], 200))
            ->withCookie($this->makeAuthCookie($token));
    }

    public function logout(): JsonResponse
    {
        $this->service->logout();

        return (new JsonResponse(
            ['message' => $this->translator->get('auth::messages.logout_success')],
            200
        ))->withCookie($this->cookieJar->forget(
            config('auth_cookie.name'),
            config('auth_cookie.path'),
            config('auth_cookie.domain'),
        ));
    }

    private function makeAuthCookie(string $token): \Symfony\Component\HttpFoundation\Cookie
    {
        return $this->cookieJar->make(
            name: config('auth_cookie.name'),
            value: $token,
            minutes: (int) config('jwt.ttl'),
            path: config('auth_cookie.path'),
            domain: config('auth_cookie.domain'),
            secure: (bool) config('auth_cookie.secure'),
            httpOnly: true,
            raw: false,
            sameSite: config('auth_cookie.same_site'),
        );
    }
}

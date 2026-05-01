<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $userData = $request->validated();

        $this->service->register(
            new RegisterDataDTO([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password']
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

        $userDto = $this->service->login(
            new LoginUserDTO([
                'email' => $credentials['email'],
                'password' => $credentials['password']
            ])
        );

        if (!$userDto) {
            return new JsonResponse(
                ['message' => $this->translator->get('auth::messages.failed')],
                401
            );
        }

        return new JsonResponse(
            [
                'user' => new LoginResponseDTO(
                    $userDto->getId(),
                    $userDto->getName(),
                    $userDto->getEmail(),
                    $this->accessApi->getPermissionsForUser($userDto->getId()),
                )
            ],
            200
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $this->service->logout();

        return new JsonResponse(
            ['message' => $this->translator->get('auth::messages.logout_success')],
            200
        );
    }
}

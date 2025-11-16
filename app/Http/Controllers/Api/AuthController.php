<?php

namespace App\Http\Controllers\Api;

use App\Data\DTO\Auth\LoginUserDTO;
use App\Data\DTO\Auth\RegisterDataDTO;
use App\Data\DTO\UserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Translation\Translator;

class AuthController extends Controller
{
    public function __construct(
        public readonly AuthService $service,
        public readonly Translator $translator,
    )
    {
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

        $request->session()->regenerate();

        return new JsonResponse(
            ['message' => $this->translator->get('auth.register_success')],
            201
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = $this->service->login(
            new LoginUserDTO([
                'email' => $credentials['email'],
                'password' => $credentials['password']
            ])
        );

        if (!$user) {
            return new JsonResponse(
                ['message' => $this->translator->get('auth.failed')],
                401
            );
        }

        $request->session()->regenerate();

        return new JsonResponse(
            ['user' => new UserDTO($user)],
            200
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $this->service->logout($request->user());

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return new JsonResponse(
            ['message' =>  $this->translator->get('auth.logout_success')],
            200
        );
    }
}

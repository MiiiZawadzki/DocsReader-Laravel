<?php

namespace Modules\Auth\Services;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Hashing\Hasher;
use Modules\Auth\DTO\LoginUserDTO;
use Modules\Auth\DTO\RegisterDataDTO;
use Modules\User\Api\UserApiInterface;
use Modules\User\DTO\UserDTO;

class AuthService
{
    public function __construct(
        private readonly UserApiInterface $userApi,
        private readonly Hasher $hasher,
        private readonly AuthFactory $auth,
    ) {
    }

    public function register(RegisterDataDTO $userData): UserDTO
    {
        return $this->userApi->createUser(
            [
                'name' => $userData->name,
                'email' => $userData->email,
                'password' => $this->hasher->make($userData->plainTextPassword),
            ]
        );
    }

    /**
     * Returns [token, UserDTO] on success, null on bad credentials.
     *
     * @return array{0: string, 1: UserDTO}|null
     */
    public function login(LoginUserDTO $credentials): ?array
    {
        $token = $this->guard()->attempt($credentials->toArray());

        if (! $token) {
            return null;
        }

        return [$token, new UserDTO($this->guard()->user())];
    }

    public function logout(): void
    {
        $this->guard()->logout();
    }

    public function refresh(): string
    {
        return $this->guard()->refresh();
    }

    private function guard(): Guard
    {
        return $this->auth->guard('jwt');
    }
}

<?php

namespace Modules\Auth\Services;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Hashing\Hasher;
use Modules\Auth\DTO\LoginUserDTO;
use Modules\Auth\DTO\RegisterDataDTO;
use Modules\User\Api\UserApiInterface;
use Modules\User\DTO\UserDTO;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWT;

class AuthService
{
    public function __construct(
        private readonly UserApiInterface $userApi,
        private readonly Hasher $hasher,
        private readonly AuthFactory $auth,
        private readonly JWT $jwt,
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
        $token = $this->jwt->getToken();
        if (!$token) {
            return;
        }

        try {
            // The refresh flow skips the expiry check, so a token that's expired but still refreshable gets revoked too
            $this->jwt->manager()->setRefreshFlow()->invalidate($token);
        } catch (JWTException) {
            // Malformed or past refresh_ttl: already unusable, nothing to revoke
        }
    }

    /**
     * @return string|null New token, or null when the token can't be refreshed
     */
    public function refresh(): ?string
    {
        try {
            return $this->guard()->refresh();
        } catch (JWTException) {
            return null;
        }
    }

    private function guard(): Guard
    {
        return $this->auth->guard('jwt');
    }
}

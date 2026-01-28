<?php

namespace Modules\Auth\Services;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Hashing\Hasher;
use Modules\Auth\DTO\LoginUserDTO;
use Modules\Auth\DTO\RegisterDataDTO;
use Modules\User\Api\UserApiInterface;
use Modules\User\Models\User;

class AuthService
{
    public function __construct(
        private UserApiInterface $userApi,
        private Hasher $hasher,
        private AuthFactory $auth
    ) {
    }

    /**
     * @param  RegisterDataDTO  $userData
     * @return User
     */
    public function register(RegisterDataDTO $userData): User
    {
        $hashedPassword = $this->generateHashedPassword($userData->plainTextPassword);

        return $this->userApi->createUser(
            [
                'name' => $userData->name,
                'email' => $userData->email,
                'password' => $hashedPassword
            ]
        );
    }

    /**
     * @param  LoginUserDTO  $credentials
     * @return User|null
     */
    public function login(LoginUserDTO $credentials): ?User
    {
        $guard = $this->auth->guard();

        if (!$guard->attempt($credentials->toArray())) {
            return null;
        }

        return $guard->user();
    }

    /**
     * @return void
     */
    public function logout(): void
    {
        $this->auth->guard('web')->logout();
    }

    /**
     * @param  string  $plainTextPassword
     * @return string
     */
    protected function generateHashedPassword(string $plainTextPassword): string
    {
        return $this->hasher->make($plainTextPassword);
    }

}

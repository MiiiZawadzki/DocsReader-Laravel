<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\DTO\LoginUserDTO;
use Modules\Auth\DTO\RegisterDataDTO;
use Modules\User\Api\UserApiInterface;
use Modules\User\Models\User;

class AuthService
{
    public function __construct(private UserApiInterface $userApi)
    {
    }

    /**
     * @param RegisterDataDTO $userData
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
     * @param LoginUserDTO $credentials
     * @return User|null
     */
    public function login(LoginUserDTO $credentials): ?User
    {
        if (!Auth::attempt($credentials->toArray())) {
            return null;
        }

        return Auth::user();
    }

    /**
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        $user->tokens()->delete();
        Auth::guard('web')->logout();
    }

    /**
     * @param string $plainTextPassword
     * @return string
     */
    protected function generateHashedPassword(string $plainTextPassword): string
    {
        return Hash::make($plainTextPassword);
    }

}

<?php

namespace App\Services;

use App\Data\DTO\Auth\CreateUserDTO;
use App\Data\DTO\Auth\LoginUserDTO;
use App\Data\DTO\Auth\RegisterDataDTO;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    /**
     * @param RegisterDataDTO $userData
     * @return User
     */
    public function register(RegisterDataDTO $userData): User
    {
        $hashedPassword = $this->generateHashedPassword($userData->plainTextPassword);

        return $this->userRepository->create(
            new CreateUserDTO([
                    'name' => $userData->name,
                    'email' => $userData->email,
                    'password' => $hashedPassword
                ]
            )
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

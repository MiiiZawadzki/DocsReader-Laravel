<?php

namespace Modules\User\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Modules\User\Repositories\Contracts\UserRepositoryInterface;

class SettingsService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    )
    {
    }

    /**
     * @param int $userId
     * @return Collection
     */
    public function data(int $userId): Collection
    {
        $user = $this->userRepository->findById($userId);

        return collect([
            'name' => $user->name,
            'email' => $user->email,
            'date' => $user->created_at->format('Y-m-d')
        ]);
    }

    /**
     * @param int $userId
     * @param string $name
     * @return bool
     */
    public function updateName(int $userId, string $name): bool
    {
        return $this->userRepository->updateName($userId, $name);
    }

    /**
     * @param int $userId
     * @param string $email
     * @return bool
     */
    public function updateEmail(int $userId, string $email): bool
    {
        return $this->userRepository->updateEmail($userId, $email);
    }

    /**
     * @param int $userId
     * @param string $password
     * @return bool
     */
    public function updatePassword(int $userId, string $password): bool
    {
        return $this->userRepository->updatePassword($userId, Hash::make($password));
    }
}

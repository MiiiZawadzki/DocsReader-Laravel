<?php

namespace Modules\User\Repositories;

use Illuminate\Support\Collection;
use Modules\User\Models\User;
use Modules\User\Repositories\Contracts\UserRepositoryInterface;

final class UserRepository implements UserRepositoryInterface
{
    /**
     * @param array $userData
     * @return User
     */
    public function create(array $userData): User
    {
        return User::create($userData);
    }

    /**
     * @param int $userId
     * @return User|null
     */
    public function findById(int $userId): ?User
    {
        return User::find($userId);
    }

    /**
     * @param array $userIds
     * @return Collection
     */
    public function getUsersName(array $userIds): Collection
    {
        return User::whereIn('id', $userIds)->pluck('name', 'id');
    }

    /**
     * @param int $userId
     * @return string|null
     */
    public function getUserName(int $userId): ?string
    {
        return User::find($userId)->getAttribute('name');
    }

    /**
     * @return Collection
     */
    public function getAll(): Collection
    {
        return User::all();
    }

    /**
     * @param int $userId
     * @param string $name
     * @return bool
     */
    public function updateName(int $userId, string $name): bool
    {
        return User::find($userId)->update(['name' => $name]);
    }

    /**
     * @param int $userId
     * @param string $email
     * @return bool
     */
    public function updateEmail(int $userId, string $email): bool
    {
        return User::find($userId)->update(['email' => $email]);
    }

    /**
     * @param int $userId
     * @param string $password
     * @return bool
     */
    public function updatePassword(int $userId, string $password): bool
    {
        return User::find($userId)->update(['password' => $password]);
    }
}

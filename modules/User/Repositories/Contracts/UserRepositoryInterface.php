<?php

namespace Modules\User\Repositories\Contracts;

use Illuminate\Support\Collection;
use Modules\User\Models\User;

interface UserRepositoryInterface
{
    /**
     * @param array $userData
     * @return User
     */
    public function create(array $userData): User;

    /**
     * @param int $userId
     * @return User|null
     */
    public function findById(int $userId): ?User;

    /**
     * @param array $userIds
     * @return Collection
     */
    public function getUsersName(array $userIds): Collection;

    /**
     * @param int $userId
     * @return string|null
     */
    public function getUserName(int $userId): ?string;

    /**
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * @param int $userId
     * @param string $name
     * @return bool
     */
    public function updateName(int $userId, string $name): bool;

    /**
     * @param int $userId
     * @param string $email
     * @return bool
     */
    public function updateEmail(int $userId, string $email): bool;

    /**
     * @param int $userId
     * @param string $password
     * @return bool
     */
    public function updatePassword(int $userId, string $password): bool;
}

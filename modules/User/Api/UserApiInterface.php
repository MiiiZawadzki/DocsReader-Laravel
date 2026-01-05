<?php

namespace Modules\User\Api;

use Illuminate\Support\Collection;
use Modules\User\DTO\UserDTO;
use Modules\User\Models\User;

interface UserApiInterface
{
    /**
     * @param int $userId
     * @return UserDTO
     */
    public function findUser(int $userId): UserDTO;

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
     * @param array $userData
     * @return User
     */
    public function createUser(array $userData): User;

    /**
     * @return Collection
     */
    public function getAllUsers(): Collection;
}

<?php

namespace Modules\User\Api;

use Illuminate\Support\Collection;
use Modules\User\DTO\UserDTO;

interface UserApiInterface
{
    /**
     * @param int $userId
     * @return UserDTO
     */
    public function findUser(int $userId): UserDTO;

    /**
     * @param array $userIds
     * @return Collection<int, string>
     */
    public function getUsersName(array $userIds): Collection;

    /**
     * @param int $userId
     * @return string|null
     */
    public function getUserName(int $userId): ?string;

    /**
     * @param array $userData
     * @return UserDTO
     */
    public function createUser(array $userData): UserDTO;

    /**
     * @return Collection<int, UserDTO>
     */
    public function getAllUsers(): Collection;
}

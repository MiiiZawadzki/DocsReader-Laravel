<?php

namespace Modules\User\Api;

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
     * @param array $userData
     * @return User
     */
    public function createUser(array $userData): User;
}

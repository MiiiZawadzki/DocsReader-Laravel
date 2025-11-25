<?php

namespace Modules\User\Repositories\Contracts;

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
}

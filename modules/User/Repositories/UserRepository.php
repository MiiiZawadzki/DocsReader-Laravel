<?php

namespace Modules\User\Repositories;

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
}

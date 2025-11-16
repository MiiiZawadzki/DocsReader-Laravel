<?php

namespace App\Repositories;

use App\Data\DTO\Auth\CreateUserDTO;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

final class UserRepository implements UserRepositoryInterface
{
    /**
     * @param CreateUserDTO $userData
     * @return User
     */
    public function create(CreateUserDTO $userData): User
    {
        return User::create($userData->toArray());
    }
}

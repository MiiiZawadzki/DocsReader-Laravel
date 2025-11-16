<?php

namespace App\Repositories\Contracts;

use App\Data\DTO\Auth\CreateUserDTO;
use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * @param CreateUserDTO $userData
     * @return User
     */
    public function create(CreateUserDTO $userData): User;
}

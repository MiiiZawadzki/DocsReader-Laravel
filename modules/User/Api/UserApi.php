<?php

namespace Modules\User\Api;

use Modules\User\DTO\UserDTO;
use Modules\User\Models\User;
use Modules\User\Repositories\Contracts\UserRepositoryInterface;

readonly class UserApi implements UserApiInterface
{
    public function __construct(public UserRepositoryInterface $repository)
    {
    }

    /**
     * @param int $userId
     * @return UserDTO
     */
    public function findUser(int $userId): UserDTO
    {
        $userData = $this->repository->findById($userId);

        return new UserDTO($userData);
    }

    /**
     * @param array $userData
     * @return User
     */
    public function createUser(array $userData): User
    {
        return $this->repository->create($userData);
    }
}

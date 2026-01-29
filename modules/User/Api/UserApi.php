<?php

namespace Modules\User\Api;

use Illuminate\Support\Collection;
use Modules\User\DTO\UserDTO;
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
     * @return UserDTO
     */
    public function createUser(array $userData): UserDTO
    {
        $user = $this->repository->create($userData);

        return new UserDTO($user);
    }

    /**
     * @param array $userIds
     * @return Collection<int, string>
     */
    public function getUsersName(array $userIds): Collection
    {
        return $this->repository->getUsersName($userIds);
    }

    /**
     * @param int $userId
     * @return string|null
     */
    public function getUserName(int $userId): ?string
    {
        return $this->repository->getUserName($userId);
    }

    /**
     * @return Collection<int, UserDTO>
     */
    public function getAllUsers(): Collection
    {
        return $this->repository->getAll()->map(fn($user) => new UserDTO($user));
    }
}

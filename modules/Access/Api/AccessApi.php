<?php

namespace Modules\Access\Api;

use InvalidArgumentException;
use Modules\Access\Repository\Contracts\AccessRepositoryInterface;

readonly class AccessApi implements AccessApiInterface
{
    public function __construct(private AccessRepositoryInterface $repository) {}

    public function getPermissionsForUser(int $userId): array
    {
        $userPermissions = $this->repository->getForUserId($userId);
        $permissions = $this->repository->getPermissions(
            array_map(fn (array $data) => $data['permission_id'], $userPermissions)
        );

        return array_map(
            fn (array $data) => $data['type'],
            $permissions
        );
    }

    /**
     * @param  int  $userId
     * @param  string  $permissionKey
     * @return bool
     */
    public function hasPermission(int $userId, string $permissionKey): bool
    {
        return in_array($permissionKey, $this->getPermissionsForUser($userId), true);
    }

    /**
     * @param  int  $userId
     * @param  string  $permissionKey
     * @return bool
     */
    public function grantPermission(int $userId, string $permissionKey): bool
    {
        $permission = $this->repository->getPermissionByType($permissionKey);

        if ($permission === null) {
            throw new InvalidArgumentException(
                sprintf('Unknown permission "%s".', $permissionKey)
            );
        }

        return $this->repository->grantPermission($userId, $permission['id']);
    }

    /**
     * @param  string  $permissionKey
     * @return bool
     */
    public function permissionExists(string $permissionKey): bool
    {
        return $this->repository->getPermissionByType($permissionKey) !== null;
    }
}

<?php

namespace Modules\Access\Repository\Contracts;

interface AccessRepositoryInterface
{
    /**
     * @param int $userId
     * @return mixed
     */
    public function getForUserId(int $userId): array;

    /**
     * @param array $permissionsId
     * @return array
     */
    public function getPermissions(array $permissionsId): array;

    /**
     * @param  string  $type
     * @return array|null
     */
    public function getPermissionByType(string $type): ?array;

    /**
     * Attaches a permission to a user.
     *
     * @param  int  $userId
     * @param  int  $permissionId
     * @return bool
     */
    public function grantPermission(int $userId, int $permissionId): bool;
}

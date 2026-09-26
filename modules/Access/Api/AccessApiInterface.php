<?php

namespace Modules\Access\Api;

use InvalidArgumentException;

interface AccessApiInterface
{
    /**
     * @param  int  $userId
     * @return array
     */
    public function getPermissionsForUser(int $userId): array;

    /**
     * @param  int  $userId
     * @param  string  $permissionKey
     * @return bool
     */
    public function hasPermission(int $userId, string $permissionKey): bool;

    /**
     * Grants a permission to a user.
     *
     * @param  int  $userId
     * @param  string  $permissionKey
     * @return bool
     *
     * @throws InvalidArgumentException when the permission does not exist
     */
    public function grantPermission(int $userId, string $permissionKey): bool;

    /**
     * @param  string  $permissionKey
     * @return bool
     */
    public function permissionExists(string $permissionKey): bool;
}

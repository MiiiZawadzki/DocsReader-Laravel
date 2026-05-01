<?php

namespace Modules\Access\Api;

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
}

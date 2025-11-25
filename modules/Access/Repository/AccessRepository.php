<?php

namespace Modules\Access\Repository;

use Modules\Access\Models\Permission;
use Modules\Access\Models\UserPermission;
use Modules\Access\Repository\Contracts\AccessRepositoryInterface;

class AccessRepository implements AccessRepositoryInterface
{
    public function getForUserId(int $userId): array
    {
        return UserPermission::where('user_id', $userId)->get()->toArray();
    }

    public function getPermissions(array $permissionsId): array
    {
        return Permission::whereIn('id', $permissionsId)->get()->toArray();
    }
}

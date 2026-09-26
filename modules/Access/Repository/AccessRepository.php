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

    public function getPermissionByType(string $type): ?array
    {
        return Permission::where('type', $type)->first()?->toArray();
    }

    public function grantPermission(int $userId, int $permissionId): bool
    {
        $userPermission = UserPermission::firstOrCreate([
            'user_id' => $userId,
            'permission_id' => $permissionId,
        ]);

        return $userPermission->wasRecentlyCreated;
    }
}

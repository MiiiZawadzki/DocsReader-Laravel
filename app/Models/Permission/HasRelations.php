<?php

namespace App\Models\Permission;

use App\Models\UserPermission;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasRelations
{
    /**
     * @return HasMany
     */
    function userPermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class, 'permission_id');
    }
}

<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Auth;
use Modules\Access\Api\AccessApiInterface;

trait AuthorizesPermissions
{
    /**
     * @param  string  $permissionKey
     * @return bool
     */
    protected function userHasPermission(string $permissionKey): bool
    {
        return app(AccessApiInterface::class)->hasPermission(Auth::id(), $permissionKey);
    }
}

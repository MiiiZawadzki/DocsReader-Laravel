<?php

namespace Modules\User\Http\Controllers;

use Modules\Access\Api\AccessApiInterface;
use Modules\User\Api\UserApiInterface;

class UserController
{
    public function __invoke(
        UserApiInterface $userApi,
        AccessApiInterface $accessApi
    ) {
        $currentUserId = \Auth::id();

        $userDto = $userApi->findUser($currentUserId);
        $permissions = $accessApi->getPermissionsForUser($currentUserId);

        return response()->json([
            'id' => $userDto->getId(),
            'name' => $userDto->getName(),
            'email' => $userDto->getEmail(),
            'permissions' => $permissions,
        ]);
    }
}

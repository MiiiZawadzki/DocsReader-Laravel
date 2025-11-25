<?php

namespace Modules\User\DTO;

use Modules\User\Models\User;

class UserDTO
{
    public string $name;
    public string $email;
//    public array $permissions;

    public function __construct(User $user)
    {
        $this->name = $user->getAttribute('name');
        $this->email = $user->getAttribute('email');
//        $this->permissions = $user->userPermissions
//            ->map(
//                fn(UserPermission $userPermission) => $userPermission->permission->type
//            )
//            ->toArray();
    }

    public function __toString(): string
    {
        return json_encode([
            'name' => $this->name,
            'email' => $this->email,
//            'permissions' => $this->permissions,
        ]);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}

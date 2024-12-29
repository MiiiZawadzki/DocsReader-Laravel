<?php

namespace App\Data\DTO;

use App\Models\User;

class UserDTO
{
    public string $name;
    public string $email;
    public array $permissions;

    public function __construct(User $user)
    {
        $this->name = $user->getAttribute('name');
        $this->email = $user->getAttribute('email');
        $this->permissions = [];
    }

    public function __toString(): string
    {
        return json_encode([
            'name' => $this->name,
            'email' => $this->email,
            'permissions' => $this->permissions,
        ]);
    }
}

<?php

namespace Modules\Auth\DTO;

final readonly class LoginResponseDTO
{

    public function __construct(public readonly string $name, public readonly string $email, public readonly array $permissions)
    {

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


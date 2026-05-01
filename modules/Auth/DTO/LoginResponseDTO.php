<?php

namespace Modules\Auth\DTO;

final readonly class LoginResponseDTO
{

    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly array $permissions
    ) {

    }

    public function __toString(): string
    {
        return json_encode([
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'permissions' => $this->permissions,
        ]);
    }
}

<?php

namespace App\Data\DTO\Auth;

final readonly class CreateUserDTO
{
    public string $name;
    public string $email;
    public string $hashedPassword;

    public function __construct(array $userData)
    {
        $this->name = $userData['name'];
        $this->email = $userData['email'];
        $this->hashedPassword = $userData['password'];
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->hashedPassword,
        ];
    }
}

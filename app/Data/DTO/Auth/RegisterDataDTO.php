<?php

namespace App\Data\DTO\Auth;

final readonly class RegisterDataDTO
{
    public string $name;
    public string $email;
    public string $plainTextPassword;

    public function __construct(array $userData)
    {
        $this->name = $userData['name'];
        $this->email = $userData['email'];
        $this->plainTextPassword = $userData['password'];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->plainTextPassword,
        ];
    }
}

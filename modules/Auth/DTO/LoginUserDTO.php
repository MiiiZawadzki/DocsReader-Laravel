<?php

namespace Modules\Auth\DTO;

final readonly class LoginUserDTO
{
    public string $email;
    public string $password;

    public function __construct(array $credentials)
    {
        $this->email = $credentials['email'];
        $this->password = $credentials['password'];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}


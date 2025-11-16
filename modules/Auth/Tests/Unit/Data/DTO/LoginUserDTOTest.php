<?php

namespace Modules\Auth\Tests\Unit\Data\DTO;

use Modules\Auth\DTO\LoginUserDTO;
use PHPUnit\Framework\TestCase;

class LoginUserDTOTest extends TestCase
{
    public function test_construct_sets_public_properties(): void
    {
        $userData = [
            'email' => 'Robert.Johnson@example.com',
            'password' => '$2y$10$examplehashedpasswordstring',
        ];

        $dto = new LoginUserDTO($userData);

        $this->assertSame($userData['email'], $dto->email);
        $this->assertSame($userData['password'], $dto->password);
    }

    public function test_to_array_returns_original_payload(): void
    {
        $userData = [
            'email' => 'sarah.williams@example.com',
            'password' => '$2y$10$anotherexamplehashedpassword',
        ];

        $dto = new LoginUserDTO($userData);

        $this->assertSame($userData, $dto->toArray());
    }
}


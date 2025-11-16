<?php

namespace Tests\Unit\Data\DTO;

use App\Data\DTO\Auth\CreateUserDTO;
use PHPUnit\Framework\TestCase;

class CreateUserDTOTest extends TestCase
{
    public function test_construct_sets_public_properties(): void
    {
        $userData = [
            'name' => 'Robert Johnson',
            'email' => 'robert.johnson@example.com',
            'password' => '$2y$10$examplehashedpasswordstring',
        ];

        $dto = new CreateUserDTO($userData);

        $this->assertSame($userData['name'], $dto->name);
        $this->assertSame($userData['email'], $dto->email);
        $this->assertSame($userData['password'], $dto->hashedPassword);
    }

    public function test_to_array_returns_original_payload(): void
    {
        $userData = [
            'name' => 'Sarah Williams',
            'email' => 'sarah.williams@example.com',
            'password' => '$2y$10$anotherexamplehashedpassword',
        ];

        $dto = new CreateUserDTO($userData);

        $this->assertSame($userData, $dto->toArray());
    }
}


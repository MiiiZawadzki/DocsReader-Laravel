<?php

namespace Tests\Unit\Data\DTO;

use App\Data\DTO\Auth\RegisterDataDTO;
use PHPUnit\Framework\TestCase;

class RegisterDataDTOTest extends TestCase
{
    public function test_construct_sets_public_properties(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'secretPassword',
        ];

        $dto = new RegisterDataDTO($userData);

        $this->assertSame($userData['name'], $dto->name);
        $this->assertSame($userData['email'], $dto->email);
        $this->assertSame($userData['password'], $dto->plainTextPassword);
    }

    public function test_to_array_returns_original_payload(): void
    {
        $userData = [
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'password' => 'anotherPassword',
        ];

        $dto = new RegisterDataDTO($userData);

        $this->assertSame($userData, $dto->toArray());
    }
}


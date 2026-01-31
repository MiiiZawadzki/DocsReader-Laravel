<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Modules\User\Api\UserApiInterface;
use Modules\User\DTO\UserDTO;

abstract class FeatureTestCase extends TestCase
{
    use LazilyRefreshDatabase;

    private UserApiInterface $userApi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userApi = app()->make(UserApiInterface::class);
    }

    /**
     * @param  array  $userData
     * @return UserDTO
     */
    protected function makeUser(array $userData = []): UserDTO
    {
        if (empty($userData)) {
            $userData = [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'password' => Hash::make('SecurePassword123!'),
            ];
        }

        return $this->userApi->createUser($userData);
    }

    /**
     * @param  string  $uri
     * @param  array  $data
     * @return TestResponse
     */
    protected function makePostJson(string $uri, array $data): TestResponse
    {
        return $this
            ->withHeaders([
//                'Referer' => 'localhost', // needed for sanctum
                'Accept' => 'application/json',
            ])
            ->postJson($uri, $data);
    }
}

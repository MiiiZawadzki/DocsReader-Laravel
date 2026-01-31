<?php

namespace Modules\Auth\Tests\Feature\Http\Requests;

use Illuminate\Support\Facades\Validator;
use Modules\Auth\Http\Requests\RegisterRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[CoversClass(RegisterRequest::class)]
#[Group('feature')]
#[Group('Auth')]
class RegisterRequestTest extends FeatureTestCase
{
    public function test_request_should_not_pass_authorization_when_user_is_logged(): void
    {
        $this->makeUser();

        $requestData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ];

        $this->postJson('/api/login', [
            'email' => $requestData['email'],
            'password' => $requestData['password'],
        ]);

        $this->assertAuthenticated('web');

        $request = new RegisterRequest($requestData);

        $this->assertFalse($request->authorize());
    }

    public function test_request_should_pass_authorization_when_user_is_not_logged(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ];

        $request = new RegisterRequest($requestData);

        $this->assertTrue($request->authorize());
    }

    public function test_request_should_pass_rules_validation(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ];

        $request = new RegisterRequest();
        $validator = Validator::make($requestData, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_request_should_not_pass_authorization_when_no_name(): void
    {
        $requestData = [
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ];

        $request = new RegisterRequest();
        $validator = Validator::make($requestData, $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_request_should_not_pass_authorization_when_no_email(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ];

        $request = new RegisterRequest();
        $validator = Validator::make($requestData, $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_request_should_not_pass_authorization_when_no_password(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password_confirmation' => 'SecurePassword123!',
        ];

        $request = new RegisterRequest();
        $validator = Validator::make($requestData, $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_request_should_not_pass_authorization_when_no_password_confirm(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
        ];

        $request = new RegisterRequest();
        $validator = Validator::make($requestData, $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_request_should_not_pass_authorization_when_empty_data(): void
    {
        $requestData = [
        ];

        $request = new RegisterRequest();
        $validator = Validator::make($requestData, $request->rules());

        $this->assertFalse($validator->passes());
    }
}

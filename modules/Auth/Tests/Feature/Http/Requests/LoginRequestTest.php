<?php

namespace Modules\Auth\Tests\Feature\Http\Requests;

use Illuminate\Support\Facades\Validator;
use Modules\Auth\Http\Requests\LoginRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[CoversClass(LoginRequest::class)]
#[Group('feature')]
#[Group('Auth')]
class LoginRequestTest extends FeatureTestCase
{
    /**
     * @return void
     */
    public function test_request_should_not_pass_authorization_when_user_is_logged(): void
    {
        $this->makeUser();

        $requestData = [
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
        ];

        $this->postJson('/api/login', $requestData);
        $this->assertAuthenticated('web');

        $request = new LoginRequest($requestData);

        $this->assertFalse($request->authorize());
    }

    /**
     * @return void
     */
    public function test_request_should_pass_authorization_when_user_is_not_logged(): void
    {
        $requestData = [
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
        ];

        $request = new LoginRequest($requestData);

        $this->assertTrue($request->authorize());
    }

    /**
     * @return void
     */
    public function test_request_should_pass_rules_validation(): void
    {
        $requestData = [
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
        ];

        $request = new LoginRequest();
        $validator = Validator::make($requestData, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /**
     * @return void
     */
    public function test_request_should_not_pass_authorization_when_no_email(): void
    {
        $requestData = [
            'password' => 'SecurePassword123!',
        ];

        $request = new LoginRequest();
        $validator = Validator::make($requestData, $request->rules());

        $this->assertFalse($validator->passes());
    }

    /**
     * @return void
     */
    public function test_request_should_not_pass_authorization_when_no_password(): void
    {
        $requestData = [
            'email' => 'john.doe@example.com',
        ];

        $request = new LoginRequest();
        $validator = Validator::make($requestData, $request->rules());

        $this->assertFalse($validator->passes());
    }

    /**
     * @return void
     */
    public function test_request_should_not_pass_authorization_when_empty_data(): void
    {
        $requestData = [
        ];

        $request = new LoginRequest();
        $validator = Validator::make($requestData, $request->rules());

        $this->assertFalse($validator->passes());
    }
}

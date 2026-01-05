<?php

namespace Modules\User\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Modules\User\Rules\MatchCurrentPassword;

class UpdateEmailRequest extends SettingsRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255|unique:users',
            'password_verification' => [
                'required',
                'string',
                new MatchCurrentPassword($this->currentPassword),
            ]
        ];
    }
}

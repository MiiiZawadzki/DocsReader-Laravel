<?php

namespace Modules\User\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Modules\User\Rules\MatchCurrentPassword;

class UpdatePasswordRequest extends SettingsRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => 'required|min:6',
            'password_verification' => [
                'required',
                'string',
                new MatchCurrentPassword($this->currentPassword),
            ]
        ];
    }
}

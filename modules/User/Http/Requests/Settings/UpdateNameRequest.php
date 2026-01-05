<?php

namespace Modules\User\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Modules\User\Rules\MatchCurrentPassword;

class UpdateNameRequest extends SettingsRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'password_verification' => [
                'required',
                'string',
                new MatchCurrentPassword($this->currentPassword),
            ]
        ];
    }
}

<?php

namespace Modules\User\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\User\Repositories\Contracts\UserRepositoryInterface;

class SettingsRequest extends FormRequest
{
    protected string $currentPassword;

    protected function prepareForValidation()
    {
        $userId = Auth::id();

        $userRepository = app()->make(UserRepositoryInterface::class);
        $user = $userRepository->findById($userId);

        $this->currentPassword = $user->getAuthPassword();
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }
}

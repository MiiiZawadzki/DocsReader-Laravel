<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\User\Http\Requests\Settings\UpdateEmailRequest;
use Modules\User\Http\Requests\Settings\UpdateNameRequest;
use Modules\User\Http\Requests\Settings\UpdatePasswordRequest;
use Modules\User\Services\SettingsService;

class SettingsController
{
    public function __construct(private readonly SettingsService $settingsService)
    {
    }

    /**
     * @return JsonResponse
     */
    public function data(): JsonResponse
    {
        try {
            $userId = Auth::id();

            return response()->json([
                'user' => $this->settingsService->data($userId),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @param UpdateNameRequest $request
     * @return JsonResponse
     */
    public function updateName(UpdateNameRequest $request): JsonResponse
    {
        try {
            $this->settingsService->updateName(
                userId: Auth::id(),
                name: $request->get('name')
            );

            return response()->json([
                'message' => __('user::messages.settings.update.success'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @param UpdateEmailRequest $request
     * @return JsonResponse
     */
    public function updateEmail(UpdateEmailRequest $request): JsonResponse
    {
        try {
            $this->settingsService->updateEmail(
                userId: Auth::id(),
                email: $request->get('email')
            );

            return response()->json([
                'message' => __('user::messages.settings.update.success'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @param UpdatePasswordRequest $request
     * @return JsonResponse
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        try {
            $this->settingsService->updatePassword(
                userId: Auth::id(),
                password: $request->get('password')
            );

            return response()->json([
                'message' => __('user::messages.settings.update.success'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}

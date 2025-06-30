<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Settings\UpdateEmailRequest;
use App\Http\Requests\Api\Settings\UpdateNameRequest;
use App\Http\Requests\Api\Settings\UpdatePasswordRequest;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
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
            $userData = $this->settingsService->data(Auth::user());
            return response()->json([
                'user' => $userData,
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
            $this->settingsService->updateName(Auth::user(), $request->get('name'));
            return response()->json([
                'message' => __('api.settings.update.success'),
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
            $this->settingsService->updateEmail(Auth::user(), $request->get('email'));

            return response()->json([
                'message' => __('api.settings.update.success'),
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
            $this->settingsService->updatePassword(Auth::user(), $request->get('password'));

            return response()->json([
                'message' => __('api.settings.update.success'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}

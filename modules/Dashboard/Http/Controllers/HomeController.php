<?php

namespace Modules\Dashboard\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Dashboard\Services\HomeService;

class HomeController
{
    public function __construct(private readonly HomeService $homeService)
    {
    }

    /**
     * @return JsonResponse
     */
    public function data(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $date = Carbon::today();

            $userData = $this->homeService->data($userId, $date);

            return response()->json([
                'data' => $userData,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}

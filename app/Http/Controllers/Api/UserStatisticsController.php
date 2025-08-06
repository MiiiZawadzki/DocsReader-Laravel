<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserStatisticsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserStatisticsController extends Controller
{
    public function __construct(private readonly UserStatisticsService $service)
    {
    }

    /**
     * @return JsonResponse
     */
    public function charts(): JsonResponse
    {
        try {
            return response()->json([
                [
                    'name' => __('api.statistics.user.read.name'),
                    'description' => __('api.statistics.user.read.description'),
                    'url' => '/api/statistics/user/read',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function readStatistics(): JsonResponse
    {
        try {
            return response()->json([
                'chart_data' => $this->service->readStatistics(Auth::user(), Carbon::now()),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}

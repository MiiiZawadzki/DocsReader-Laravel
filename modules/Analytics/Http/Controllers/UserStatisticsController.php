<?php

namespace Modules\Analytics\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Analytics\Services\UserStatisticsService;

class UserStatisticsController
{
    public function __construct(private readonly UserStatisticsService $service)
    {
    }

    /**
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            return response()->json([
                [
                    'name' => __('analytics::messages.statistics.user.active.name'),
                    'description' => __('analytics::messages.statistics.user.active.description'),
                    'url' => '/api/statistics/user/documents/active',
                ],
                [
                    'name' => __('analytics::messages.statistics.user.total.name'),
                    'description' => __('analytics::messages.statistics.user.total.description'),
                    'url' => '/api/statistics/user/documents/total',
                ],
                [
                    'name' => __('analytics::messages.statistics.user.read.name'),
                    'description' => __('analytics::messages.statistics.user.read.description'),
                    'url' => '/api/statistics/user/documents/read',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function charts(): JsonResponse
    {
        try {
            return response()->json([
                [
                    'name' => __('analytics::messages.statistics.user.read.name'),
                    'description' => __('analytics::messages.charts.user.read.description'),
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
            $userId = Auth::id();

            return response()->json([
                'chart_data' => $this->service->readStatistics($userId),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function activeDocuments(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $date = Carbon::now();

            return response()->json([
                'value' => $this->service->activeDocuments($userId, $date),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function totalDocuments(): JsonResponse
    {
        try {
            $userId = Auth::id();

            return response()->json([
                'value' => $this->service->totalDocuments($userId),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function readDocuments(): JsonResponse
    {
        try {
            $userId = Auth::id();

            return response()->json([
                'value' => $this->service->readDocuments($userId),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}

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
                    'name' => __('api.statistics.user.active.name'),
                    'description' => __('api.statistics.manage.active.description'),
                    'url' => '/api/statistics/user/documents/active',
                ],
                [
                    'name' => __('api.statistics.user.total.name'),
                    'description' => __('api.statistics.manage.total.description'),
                    'url' => '/api/statistics/user/documents/total',
                ],
                [
                    'name' => __('api.statistics.user.read.name'),
                    'description' => __('api.statistics.user.read.description'),
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
                    'name' => __('api.statistics.user.read.name'),
                    'description' => __('api.charts.user.read.description'),
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

    /**
     * @return JsonResponse
     */
    public function activeDocuments(): JsonResponse
    {
        try {
            return response()->json([
                'value' => $this->service->activeDocuments(Auth::user(), Carbon::now()),
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
            return response()->json([
                'value' => $this->service->totalDocuments(Auth::user()),
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
            return response()->json([
                'value' => $this->service->readDocuments(Auth::user()),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}

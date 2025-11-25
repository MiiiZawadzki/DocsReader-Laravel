<?php

namespace Modules\Analytics\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Analytics\Services\ManageStatisticsService;

class ManageStatisticsController
{
    public function __construct(private readonly ManageStatisticsService $service)
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
                    'name' => __('api.statistics.manage.active.name'),
                    'description' => __('api.statistics.manage.active.description'),
                    'url' => '/api/statistics/manage/documents/active',
                ],
                [
                    'name' => __('api.statistics.manage.total.name'),
                    'description' => __('api.statistics.manage.total.description'),
                    'url' => '/api/statistics/manage/documents/total',
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
                    'name' => __('api.charts.manage.read.name'),
                    'description' => __('api.charts.manage.read.description'),
                    'url' => '/api/statistics/manage/read',
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
}

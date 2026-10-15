<?php

namespace Modules\Analytics\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Analytics\Http\Requests\ManageStatisticsRequest;
use Modules\Analytics\Services\ManageStatisticsService;

class ManageStatisticsController
{
    public function __construct(private readonly ManageStatisticsService $service)
    {
    }

    /**
     * @param ManageStatisticsRequest $request
     * @return JsonResponse
     */
    public function stats(ManageStatisticsRequest $request): JsonResponse
    {
        try {
            return response()->json([
                [
                    'name' => __('analytics::messages.statistics.manage.active.name'),
                    'description' => __('analytics::messages.statistics.manage.active.description'),
                    'url' => '/api/statistics/manage/documents/active',
                ],
                [
                    'name' => __('analytics::messages.statistics.manage.total.name'),
                    'description' => __('analytics::messages.statistics.manage.total.description'),
                    'url' => '/api/statistics/manage/documents/total',
                ],
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json(['message' => __('common::messages.errors.unexpected')], 500);
        }
    }

    /**
     * @param ManageStatisticsRequest $request
     * @return JsonResponse
     */
    public function charts(ManageStatisticsRequest $request): JsonResponse
    {
        try {
            return response()->json([
                [
                    'name' => __('analytics::messages.charts.manage.read.name'),
                    'description' => __('analytics::messages.charts.manage.read.description'),
                    'url' => '/api/statistics/manage/read',
                ],
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json(['message' => __('common::messages.errors.unexpected')], 500);
        }
    }

    /**
     * @param ManageStatisticsRequest $request
     * @return JsonResponse
     */
    public function readStatistics(ManageStatisticsRequest $request): JsonResponse
    {
        try {
            $userId = Auth::id();

            return response()->json([
                'chart_data' => $this->service->readStatistics($userId),
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json(['message' => __('common::messages.errors.unexpected')], 500);
        }
    }

    /**
     * @param ManageStatisticsRequest $request
     * @return JsonResponse
     */
    public function activeDocuments(ManageStatisticsRequest $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $date = Carbon::today();

            return response()->json([
                'value' => $this->service->activeDocuments($userId, $date),
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json(['message' => __('common::messages.errors.unexpected')], 500);
        }
    }

    /**
     * @param ManageStatisticsRequest $request
     * @return JsonResponse
     */
    public function totalDocuments(ManageStatisticsRequest $request): JsonResponse
    {
        try {
            $userId = Auth::id();

            return response()->json([
                'value' => $this->service->totalDocuments($userId),
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json(['message' => __('common::messages.errors.unexpected')], 500);
        }
    }
}

<?php

namespace Modules\Analytics\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Analytics\Services\DocumentStatisticsService;


class DocumentStatisticsController
{
    public function __construct(private readonly DocumentStatisticsService $service)
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $documentId = $request->route('document');

            return response()->json([
                [
                    'name' => __('api.statistics.document.assigned.name'),
                    'description' => __('api.statistics.document.assigned.description'),
                    'url' => "/api/statistics/manage/document/$documentId/assigned",
                ],
                [
                    'name' => __('api.statistics.document.reads.name'),
                    'description' => __('api.statistics.document.reads.description'),
                    'url' => "/api/statistics/manage/document/$documentId/reads",
                ],
                [
                    'name' => __('api.statistics.document.ratio.name'),
                    'description' => __('api.statistics.document.ratio.description'),
                    'url' => "/api/statistics/manage/document/$documentId/ratio",
                    'isDecimal' => true
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function charts(Request $request): JsonResponse
    {
        $documentId = $request->route('document');

        try {
            return response()->json([
                [
                    'name' => __('api.charts.document.read.name'),
                    'description' => __('api.charts.document.read.description'),
                    'url' => "/api/statistics/manage/document/$documentId/read",
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function readStatistics(Request $request): JsonResponse
    {
        try {
//            $document = Document::where('uuid', $request->route('document'))->first();
//
//            return response()->json([
//                'chart_data' => $this->documentService->readStatistics($document),
//            ]);
            return response()->json([
                'chart_data' => $this->service->readStatistics(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function documentReads(Request $request): JsonResponse
    {
        try {
//            $document = Document::where('uuid', $request->route('document'))->first();

            return response()->json([
                'value' => $this->service->documentReads(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function documentAssignment(Request $request): JsonResponse
    {
        try {
//            $document = Document::where('uuid', $request->route('document'))->first();

            return response()->json([
                'value' => $this->service->documentAssignment(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function documentReadRatio(Request $request): JsonResponse
    {
        try {
//            $document = Document::where('uuid', $request->route('document'))->first();

            return response()->json([
                'value' => $this->service->documentReadRatio(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}

<?php

namespace Modules\Analytics\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Analytics\Services\DocumentStatisticsService;
use Modules\Document\Api\DocumentApiInterface;

class DocumentStatisticsController
{
    public function __construct(
        private readonly DocumentStatisticsService $service,
        private readonly DocumentApiInterface $documentApi
    )
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
                    'name' => __('analytics::messages.statistics.document.assigned.name'),
                    'description' => __('analytics::messages.statistics.document.assigned.description'),
                    'url' => "/api/statistics/manage/document/$documentId/assigned",
                ],
                [
                    'name' => __('analytics::messages.statistics.document.reads.name'),
                    'description' => __('analytics::messages.statistics.document.reads.description'),
                    'url' => "/api/statistics/manage/document/$documentId/reads",
                ],
                [
                    'name' => __('analytics::messages.statistics.document.ratio.name'),
                    'description' => __('analytics::messages.statistics.document.ratio.description'),
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
                    'name' => __('analytics::messages.charts.document.read.name'),
                    'description' => __('analytics::messages.charts.document.read.description'),
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
            $documentUuid = $request->route('document');
            $documentDto = $this->documentApi->getDocumentByUuid($documentUuid);

            return response()->json([
                'chart_data' => $this->service->readStatistics($documentDto->id),
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
            $documentUuid = $request->route('document');
            $documentDto = $this->documentApi->getDocumentByUuid($documentUuid);

            return response()->json([
                'value' => $this->service->documentReads($documentDto->id),
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
            $documentUuid = $request->route('document');
            $documentDto = $this->documentApi->getDocumentByUuid($documentUuid);

            return response()->json([
                'value' => $this->service->documentAssignment($documentDto->id),
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
            $documentUuid = $request->route('document');
            $documentDto = $this->documentApi->getDocumentByUuid($documentUuid);

            return response()->json([
                'value' => $this->service->documentReadRatio($documentDto->id),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}

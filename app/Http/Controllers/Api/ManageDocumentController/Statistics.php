<?php

namespace App\Http\Controllers\Api\ManageDocumentController;

use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait Statistics
{
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
            $document = Document::where('uuid', $request->route('document'))->first();

            return response()->json([
                'chart_data' => $this->documentService->readStatistics($document),
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
            $document = Document::where('uuid', $request->route('document'))->first();

            return response()->json([
                'value' => $this->documentService->documentReads($document),
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
            $document = Document::where('uuid', $request->route('document'))->first();

            return response()->json([
                'value' => $this->documentService->documentAssignment($document),
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
            $document = Document::where('uuid', $request->route('document'))->first();

            return response()->json([
                'value' => $this->documentService->documentReadRatio($document),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}

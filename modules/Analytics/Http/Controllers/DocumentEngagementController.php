<?php

namespace Modules\Analytics\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Analytics\Http\Requests\DocumentEngagementRequest;
use Modules\Analytics\Http\Requests\DocumentEngagementSessionsRequest;
use Modules\Analytics\Http\Resources\DocumentEngagementHeatmapResource;
use Modules\Analytics\Http\Resources\DocumentEngagementSessionResource;
use Modules\Analytics\Http\Resources\DocumentEngagementSummaryResource;
use Modules\Analytics\Services\DocumentEngagementReportingService;
use Modules\Document\Api\DocumentApiInterface;

readonly class DocumentEngagementController
{
    public function __construct(
        private DocumentEngagementReportingService $service,
        private DocumentApiInterface $documentApi,
    ) {}

    /**
     * @param  DocumentEngagementRequest  $request
     * @return JsonResource|JsonResponse
     */
    public function summary(DocumentEngagementRequest $request): JsonResource|JsonResponse
    {
        $documentDto = $this->documentApi->getDocumentByUuid($request->route('document'));
        if ($documentDto === null) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        return DocumentEngagementSummaryResource::make($this->service->summary($documentDto->id));
    }

    /**
     * @param  DocumentEngagementRequest  $request
     * @return JsonResource|JsonResponse
     */
    public function heatmap(DocumentEngagementRequest $request): JsonResource|JsonResponse
    {
        $documentDto = $this->documentApi->getDocumentByUuid($request->route('document'));
        if ($documentDto === null) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        return DocumentEngagementHeatmapResource::make([
            'pages' => $this->service->pageHeatmap($documentDto->id),
            'totalPages' => $documentDto->totalPages,
            'minSecondsPerPage' => $documentDto->delay,
        ]);
    }

    /**
     * @param  DocumentEngagementSessionsRequest  $request
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function sessions(DocumentEngagementSessionsRequest $request): AnonymousResourceCollection|JsonResponse
    {
        $documentDto = $this->documentApi->getDocumentByUuid($request->route('document'));
        if ($documentDto === null) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        $paginator = $this->service->sessions(
            $documentDto->id,
            (int) $request->input('page', 1),
            (int) $request->input('per_page', 20),
        );

        return DocumentEngagementSessionResource::collection($paginator);
    }
}

<?php

namespace Modules\History\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\History\Http\Requests\GetHistoryRequest;
use Modules\History\Services\DocumentHistoryService;

class DocumentsHistoryController
{
    public function __construct(private readonly DocumentHistoryService $documentHistoryService)
    {
    }

    /**
     * @param GetHistoryRequest $request
     * @return JsonResponse
     */
    public function data(GetHistoryRequest $request): JsonResponse
    {
        try {
            $documentHistoryData = $this->documentHistoryService->get();
//            0/0;
            return response()->json([
                'history' => $documentHistoryData,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}

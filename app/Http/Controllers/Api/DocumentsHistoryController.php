<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DocumentsHistory\GetHistoryRequest;
use App\Services\DocumentHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DocumentsHistoryController extends Controller
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
            $documentHistoryData = $this->documentHistoryService->get(Auth::user());

            return response()->json([
                'history' => $documentHistoryData,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}

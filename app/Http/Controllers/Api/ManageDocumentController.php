<?php

namespace App\Http\Controllers\Api;

use App\Data\DTO\UpdateDocumentDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Document\UpdateRequest;
use App\Http\Requests\Api\ManageDocument\ShowRequest;
use App\Models\Document;
use App\Services\ManageDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ManageDocumentController extends Controller
{
    public function __construct(private readonly ManageDocumentService $documentService)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $documents = $this->documentService->get(Auth::user());
            return response()->json([
                'documents' => $documents,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param ShowRequest $request
     * @return JsonResponse
     */
    public function show(ShowRequest $request): JsonResponse
    {
        try {
            $document = Document::where('uuid', $request->route('document'))->first();
            $documentData = $this->documentService->show($document, Auth::user());
            return response()->json([
                'document' => $documentData,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request): JsonResponse
    {
        try {
            $document = $this->documentService->update(new UpdateDocumentDTO($request->route('document'), $request));
            return response()->json([
                'message' => __('api.document.update.success', ['name' => $document->getAttribute('name')]),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}

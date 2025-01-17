<?php

namespace App\Http\Controllers\Api;

use App\Data\DTO\CreateDocumentDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Document\StoreRequest;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function __construct(private readonly DocumentService $documentService)
    {
    }

    /**
     * Display a listing of the resource.
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // TODO
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        try {
            $document = $this->documentService->store(new CreateDocumentDTO($request));
            return response()->json([
                'message' => __('api.document.store.success', ['name' => $document->getAttribute('name')]),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // TODO
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // TODO
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // TODO
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // TODO
    }
}

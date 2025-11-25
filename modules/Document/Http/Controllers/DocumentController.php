<?php

namespace Modules\Document\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Document\Http\Requests\MarkReadRequest;
use Modules\Document\Http\Requests\ShowRequest;
use Modules\Document\Http\Requests\StoreRequest;
use Modules\Document\Services\DocumentService;

class DocumentController
{
    public function __construct(private readonly DocumentService $documentService)
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // TODO
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        try {
//            $document = $this->documentService->store(new CreateDocumentDTO($request));
//            return response()->json([
//                'message' => __('api.document.store.success', ['name' => $document->getAttribute('name')]),
//            ]);
            return response()->json([
                'message' => __('api.document.store.success', ['name' => 'asd']),
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
//            $document = Document::where('uuid', $request->route('document'))->first();
//            $documentData = $this->documentService->show($document, Auth::user());

//            return response()->json([
//                'document' => $documentData,
//            ]);
            return response()->json([
                'document' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark document as read
     *
     * @param MarkReadRequest $request
     * @return JsonResponse
     */
    public function markAsRead(MarkReadRequest $request): JsonResponse
    {
        try {
//            $document = Document::where('uuid', $request->route('document'))->first();
//            $documentRead = $this->documentService->markRead($document, Auth::user());
//
//            return response()->json([
//                'message' => __('api.document.read.success', ['name' => $document->getAttribute('name')]),
//                'date' => $documentRead->created_at->format('Y-m-d'),
//            ]);

            return response()->json([
                'message' => __('api.document.read.success', ['name' =>'asd']),
                'date' => now()->format('Y-m-d'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}

<?php

namespace Modules\Document\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Document\DTO\CreateDocumentDTO;
use Modules\Document\Http\Requests\ShowRequest;
use Modules\Document\Http\Requests\StoreRequest;
use Modules\Document\Models\Document;
use Modules\Document\Services\DocumentService;
use Modules\Document\Transformers\IndexDocumentsDataTransformer;
use Modules\Document\Transformers\ShowDocumentDataTransformer;
use Modules\History\Api\HistoryApiInterface;
use Modules\User\Api\UserApiInterface;

readonly class DocumentController
{
    public function __construct(
        private DocumentService     $documentService,
        private HistoryApiInterface $historyApi,
        private UserApiInterface    $userApi,
    )
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
            $userId = Auth::id();

            $documents = $this->documentService->getForUser($userId);
            $documentIds = $documents->pluck('id')->toArray();
            $authorIds = $documents->pluck('user_id')->unique()->toArray();

            $readStatuses = $this->historyApi->getReadStatusForDocuments($userId, $documentIds);
            $authorTags = $this->userApi->getUsersName($authorIds);

            $result = $documents->map(
                fn(Document $document) => IndexDocumentsDataTransformer::transform(
                    $document,
                    $readStatuses,
                    $authorTags
                )
            );

            return response()->json([
                'documents' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
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
            $document = $this->documentService->store(new CreateDocumentDTO($request));

            return response()->json([
                'message' => __(
                    'document::messages.store.success',
                    ['name' => $document->getAttribute('name')]
                ),
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
            $userId = Auth::id();
            $documentUuid = $request->route('document');

            $document = $this->documentService->getDocumentByUuid($documentUuid);

            $readStatus = $this->historyApi->getReadStatusForDocument($userId, $document->getKey());
            $authorTag = $this->userApi->getUserName($document->getAttribute('user_id'));

            $documentData = ShowDocumentDataTransformer::transform(
                $document,
                $readStatus->createdAt,
                $authorTag
            );

            return response()->json([
                'document' => $documentData,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}

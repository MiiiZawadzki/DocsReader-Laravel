<?php

namespace Modules\Document\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Modules\Document\Aggregators\DocumentListAggregator;
use Modules\Document\DTO\CreateDocumentDTO;
use Modules\Document\Http\Requests\IndexRequest;
use Modules\Document\Http\Requests\ShowRequest;
use Modules\Document\Http\Requests\StoreRequest;
use Modules\Document\Http\Resources\DocumentResource;
use Modules\Document\Services\DocumentService;
use Modules\Document\Transformers\ShowDocumentDataTransformer;
use Modules\History\Api\HistoryApiInterface;
use Modules\User\Api\UserApiInterface;

readonly class DocumentController
{
    public function __construct(
        private DocumentService $documentService,
        private HistoryApiInterface $historyApi,
        private UserApiInterface $userApi,
        private DocumentListAggregator $documentListAggregator,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @param  IndexRequest  $request
     * @return AnonymousResourceCollection
     */
    public function index(IndexRequest $request): AnonymousResourceCollection
    {
        $items = $this->documentListAggregator->getForUser(Auth::id(), $request->toFilters());

        return DocumentResource::collection($items);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreRequest  $request
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
     * @param  ShowRequest  $request
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

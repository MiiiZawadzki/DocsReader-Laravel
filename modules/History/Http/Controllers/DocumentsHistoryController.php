<?php

namespace Modules\History\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Document\Api\DocumentApiInterface;
use Modules\History\Api\HistoryApiInterface;
use Modules\History\Http\Requests\GetHistoryRequest;
use Modules\History\Http\Requests\MarkReadRequest;
use Modules\History\Models\DocumentRead;
use Modules\History\Repositories\DocumentReadRepository;
use Modules\History\Transformers\DocumentsHistoryDataTransformer;
use Modules\User\Api\UserApiInterface;

class DocumentsHistoryController
{
    public function __construct(
        private readonly DocumentApiInterface   $documentApi,
        private readonly DocumentReadRepository $documentReadRepository,
        private HistoryApiInterface             $historyApi,
        private UserApiInterface                $userApi,
    )
    {
    }

    /**
     * @param GetHistoryRequest $request
     * @return JsonResponse
     */
    public function data(GetHistoryRequest $request): JsonResponse
    {
        try {
            $userId = Auth::id();

            $documents = $this->documentReadRepository->getReadDocuments($userId);
            $documentsDto = $this->documentApi->getDocumentsById($documents->pluck('document_id')->toArray());

            $documentIds = $documentsDto->pluck('id')->toArray();
            $authorIds = $documentsDto->pluck('userId')->unique()->toArray();

            $readStatuses = $this->historyApi->getReadStatusForDocuments($userId, $documentIds);
            $authorTags = $this->userApi->getUsersName($authorIds);

            $result = $documents->map(
                fn(DocumentRead $documentRead) => DocumentsHistoryDataTransformer::transform(
                    $documentsDto->where('id', $documentRead->getAttribute('document_id'))->first(),
                    $readStatuses,
                    $authorTags,
                )
            );

            return response()->json([
                'history' => $result,
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
            $documentUuid = $request->route('document');
            $documentDto = $this->documentApi->getDocumentByUuid($documentUuid);

            $userId = Auth::id();

            $documentRead = $this->documentReadRepository->markAsRead($documentDto->id, $userId);

            return response()->json([
                'message' => __('history::messages.read.success', ['name' => $documentDto->name]),
                'date' => $documentRead->created_at->format('Y-m-d'),
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}

<?php

namespace Modules\History\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Modules\Document\Api\DocumentApiInterface;
use Modules\History\Aggregators\HistoryListAggregator;
use Modules\History\Events\DocumentRead as DocumentReadEvent;
use Modules\History\Http\Requests\GetHistoryRequest;
use Modules\History\Http\Requests\MarkReadRequest;
use Modules\History\Http\Resources\HistoryItemResource;
use Modules\History\Repositories\DocumentReadRepository;
use Modules\User\Api\UserApiInterface;

class DocumentsHistoryController
{
    public function __construct(
        private readonly DocumentApiInterface $documentApi,
        private readonly DocumentReadRepository $documentReadRepository,
        private readonly UserApiInterface $userApi,
        private readonly HistoryListAggregator $historyListAggregator,
    ) {
    }

    /**
     * @param  GetHistoryRequest  $request
     * @return AnonymousResourceCollection
     */
    public function data(GetHistoryRequest $request): AnonymousResourceCollection
    {
        $items = $this->historyListAggregator->getForUser(Auth::id(), $request->toFilters());

        return HistoryItemResource::collection($items);
    }

    /**
     * Mark document as read
     *
     * @param  MarkReadRequest  $request
     * @return JsonResponse
     */
    public function markAsRead(MarkReadRequest $request): JsonResponse
    {
        try {
            $documentUuid = $request->route('document');
            $documentDto = $this->documentApi->getDocumentByUuid($documentUuid);

            $userId = Auth::id();

            $documentRead = $this->documentReadRepository->markAsRead($documentDto->id, $userId);

            if ($documentRead->wasRecentlyCreated) {
                DocumentReadEvent::dispatch(
                    $documentUuid,
                    $documentDto->name,
                    $userId,
                    $this->userApi->getUserName($userId) ?? '',
                    $documentRead->created_at->toIso8601String(),
                    $this->documentReadRepository->getDocumentReadCount($documentDto->id),
                );
            }

            return response()->json([
                'message' => __('history::messages.read.success', ['name' => $documentDto->name]),
                'date' => $documentRead->created_at->format('Y-m-d'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}

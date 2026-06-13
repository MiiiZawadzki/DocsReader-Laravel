<?php

namespace Modules\History\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Document\Api\DocumentApiInterface;
use Modules\Engagement\Api\EngagementApiInterface;
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
        private readonly EngagementApiInterface $engagementApi,
    ) {}

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

            $sessionUuid = $request->input('session_uuid');
            $sessionId = $sessionUuid
                ? $this->engagementApi->findSessionIdByUuid($sessionUuid, $userId)
                : null;
            $snapshot = $this->engagementApi->snapshot($userId, $documentDto->id);

            $documentRead = $this->documentReadRepository->markAsRead(
                $documentDto->id,
                $userId,
                $sessionId,
                $snapshot['totalActiveSeconds'] ?: null,
                $snapshot['pagesViewedCount'] ?: null,
            );

            if ($documentRead->wasRecentlyCreated) {
                try {
                    DocumentReadEvent::dispatch(
                        $documentUuid,
                        $documentDto->name,
                        $userId,
                        $this->userApi->getUserName($userId) ?? '',
                        $documentRead->created_at->toIso8601String(),
                        $this->documentReadRepository->getDocumentReadCount($documentDto->id),
                        $snapshot['totalActiveSeconds'],
                        $snapshot['pagesViewedCount'],
                    );
                } catch (\Throwable $broadcastError) {
                    Log::warning('DocumentRead broadcast failed', [
                        'document_uuid' => $documentUuid,
                        'user_id' => $userId,
                        'error' => $broadcastError->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'message' => __('history::messages.read.success', ['name' => $documentDto->name]),
                'date' => $documentRead->created_at->format('Y-m-d'),
                'certificateId' => $documentRead->certificate_id,
            ]);
        } catch (\Exception $e) {
            Log::error('markAsRead failed', [
                'document_uuid' => $request->route('document'),
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => __('history::messages.read.error')], 500);
        }
    }
}

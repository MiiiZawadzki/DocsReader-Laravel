<?php

namespace Modules\Engagement\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Document\Api\DocumentApiInterface;
use Modules\Engagement\Http\Requests\EndSessionRequest;
use Modules\Engagement\Http\Requests\RecordTicksRequest;
use Modules\Engagement\Http\Requests\StartSessionRequest;
use Modules\Engagement\Http\Resources\StartSessionResource;
use Modules\Engagement\Repositories\Contracts\PageProgressRepositoryInterface;
use Modules\Engagement\Services\EngagementRecorder;

readonly class ReadingSessionController
{
    public function __construct(
        private EngagementRecorder $recorder,
        private PageProgressRepositoryInterface $progress,
        private DocumentApiInterface $documentApi,
    ) {}

    /**
     * @param  StartSessionRequest  $request
     * @return StartSessionResource
     */
    public function start(StartSessionRequest $request): StartSessionResource
    {
        $documentDto = $this->documentApi->getDocumentByUuid($request->input('documentUuid'));

        $session = $this->recorder->start(
            Auth::id(),
            $documentDto->id,
            $request->input('clientMeta'),
        );

        return StartSessionResource::make([
            'session' => $session,
            'document' => $documentDto,
            'pageProgress' => $this->progress->forUserDocument(Auth::id(), $documentDto->id),
        ]);
    }

    /**
     * @param  RecordTicksRequest  $request
     * @return JsonResponse
     */
    public function record(RecordTicksRequest $request): JsonResponse
    {
        // Reuses the session already resolved (and authorized) by the request.
        $session = $request->readingSession();

        $result = $this->recorder->record($session, $request->input('ticks', []));

        return response()->json([
            'accepted' => $result['accepted'],
            'last_page' => $result['lastPage'],
        ], 202);
    }

    /**
     * @param  EndSessionRequest  $request
     * @return JsonResponse
     */
    public function end(EndSessionRequest $request): JsonResponse
    {
        $session = $request->readingSession();

        $this->recorder->end($session);

        return response()->json(['ended' => true]);
    }
}

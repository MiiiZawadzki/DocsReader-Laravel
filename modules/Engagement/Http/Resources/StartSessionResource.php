<?php

namespace Modules\Engagement\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Document\DTO\DocumentDTO;
use Modules\Engagement\Models\ReadingSession;

class StartSessionResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ReadingSession $session */
        $session = $this->resource['session'];
        /** @var DocumentDTO $document */
        $document = $this->resource['document'];

        return [
            'session_uuid' => $session->uuid,
            'resume_page' => (int) $session->last_page,
            'total_pages' => $document->totalPages,
            'min_seconds_per_page' => $document->delay,
            'page_progress' => PageProgressResource::collection($this->resource['pageProgress']),
        ];
    }
}

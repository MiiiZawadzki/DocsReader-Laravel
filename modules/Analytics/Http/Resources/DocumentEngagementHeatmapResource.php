<?php

namespace Modules\Analytics\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentEngagementHeatmapResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'pages' => PageHeatmapResource::collection($this->resource['pages']),
            'total_pages' => $this->resource['totalPages'],
            'min_seconds_per_page' => $this->resource['minSecondsPerPage'],
        ];
    }
}

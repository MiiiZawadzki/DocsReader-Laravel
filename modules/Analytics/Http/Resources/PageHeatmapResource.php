<?php

namespace Modules\Analytics\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageHeatmapResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $page = $this->resource;

        return [
            'page_number' => $page['pageNumber'],
            'avg_seconds' => $page['avgSeconds'],
            'viewer_count' => $page['viewerCount'],
        ];
    }
}

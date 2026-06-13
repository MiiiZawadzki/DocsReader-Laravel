<?php

namespace Modules\Engagement\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageProgressResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'page_number' => (int) $this->resource->page_number,
            'total_active_seconds' => (int) $this->resource->total_active_seconds,
        ];
    }
}

<?php

namespace Modules\History\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoryStatusResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Carbon|null $readAt */
        $readAt = $this->resource;

        if ($readAt === null) {
            return [
                'read' => false,
                'name' => __('common::messages.statuses.new'),
                'date' => null,
            ];
        }

        return [
            'read' => true,
            'name' => __('common::messages.statuses.read'),
            'date' => $readAt->format('Y-m-d'),
        ];
    }
}

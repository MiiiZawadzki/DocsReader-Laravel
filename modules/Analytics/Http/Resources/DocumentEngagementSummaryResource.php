<?php

namespace Modules\Analytics\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentEngagementSummaryResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $summary = $this->resource;

        return [
            'total_sessions' => $summary['totalSessions'],
            'avg_total_seconds' => $summary['avgTotalSeconds'],
            'avg_pages_viewed' => $summary['avgPagesViewed'],
            'completion_rate' => $summary['completionRate'],
            'skim_rate' => $summary['skimRate'],
            'min_seconds_per_page' => $summary['minSecondsPerPage'],
            'total_pages' => $summary['totalPages'],
        ];
    }
}

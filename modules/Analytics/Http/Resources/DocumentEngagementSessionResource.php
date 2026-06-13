<?php

namespace Modules\Analytics\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A single reading-session row in the engagement dashboard
 */
class DocumentEngagementSessionResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $session = $this->resource;

        return [
            'session_uuid' => $session['sessionUuid'],
            'user_id' => $session['userId'],
            'user_name' => $session['userName'],
            'started_at' => $session['startedAt'],
            'last_tick_at' => $session['lastTickAt'],
            'ended_at' => $session['endedAt'],
            'total_active_seconds' => $session['totalActiveSeconds'],
            'last_page' => $session['lastPage'],
            'furthest_page' => $session['furthestPage'],
            'confirmed_at' => $session['confirmedAt'],
            'user_has_confirmed' => $session['userHasConfirmed'],
        ];
    }
}

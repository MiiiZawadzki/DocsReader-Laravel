<?php

namespace Modules\Engagement\Models;

use Illuminate\Database\Eloquent\Model;

class ReadingSession extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'document_id',
        'started_at',
        'last_tick_at',
        'ended_at',
        'total_active_seconds',
        'last_page',
        'client_meta',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'last_tick_at' => 'datetime',
            'ended_at' => 'datetime',
            'total_active_seconds' => 'integer',
            'last_page' => 'integer',
            'client_meta' => 'array',
        ];
    }
}

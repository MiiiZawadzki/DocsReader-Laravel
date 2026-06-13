<?php

namespace Modules\Engagement\Models;

use Illuminate\Database\Eloquent\Model;

class ReadingPageTick extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'reading_session_id',
        'user_id',
        'document_id',
        'page_number',
        'client_event_id',
        'active_ms',
        'occurred_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'page_number' => 'integer',
            'active_ms' => 'integer',
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}

<?php

namespace Modules\Engagement\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentPageProgress extends Model
{
    protected $table = 'document_page_progress';

    // first_viewed_at / last_viewed_at carry the logic
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'document_id',
        'page_number',
        'total_active_seconds',
        'first_viewed_at',
        'last_viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'page_number' => 'integer',
            'total_active_seconds' => 'integer',
            'first_viewed_at' => 'datetime',
            'last_viewed_at' => 'datetime',
        ];
    }
}

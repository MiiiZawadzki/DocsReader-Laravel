<?php

namespace Modules\History\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRead extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'document_id',
        'user_id',
        'confirmed',
        'certificate_id',
        'confirmed_at',
        'total_active_seconds',
        'pages_viewed_count',
        'last_session_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'confirmed' => 'boolean',
            'total_active_seconds' => 'integer',
            'pages_viewed_count' => 'integer',
            'last_session_id' => 'integer',
        ];
    }
}

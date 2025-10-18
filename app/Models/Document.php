<?php

namespace App\Models;

use App\Models\Document\HasRelations;
use App\Models\Document\HasScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperDocument
 */
class Document extends Model
{
    use HasRelations, HasScopes, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'uuid',
        'source_name',
        'description',
        'user_id',
        'file_path',
        'date_from',
        'date_to',
        'declaration_message',
        'delay',
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
            'date_from' => 'datetime',
            'date_to' => 'datetime',
            'delay' => 'integer',
        ];
    }
}

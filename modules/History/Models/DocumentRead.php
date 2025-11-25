<?php

namespace Modules\History\Models;

use App\Models\DocumentRead\HasRelations;
use Illuminate\Database\Eloquent\Model;

class DocumentRead extends Model
{
//    use HasRelations;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'document_id',
        'user_id',
        'confirmed',
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
            'confirmed' => 'boolean',
        ];
    }
}

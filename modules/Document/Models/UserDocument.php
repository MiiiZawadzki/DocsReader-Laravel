<?php

namespace Modules\Document\Models;

use App\Models\UserDocument\HasRelations;
use Illuminate\Database\Eloquent\Model;

class UserDocument extends Model
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
        'created_by',
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
        ];
    }
}

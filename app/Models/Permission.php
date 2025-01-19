<?php

namespace App\Models;

use App\Models\Permission\HasRelations;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasRelations;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
    ];
}

<?php

namespace App\Models;

use App\Models\UserPermission\HasRelations;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    use HasRelations;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'permission_id',
    ];
}

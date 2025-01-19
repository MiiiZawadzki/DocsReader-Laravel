<?php

namespace App\Models\UserPermission;

use App\Models\Document;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasRelations
{
    /**
     * @return BelongsTo
     */
    function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo
     */
    function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }
}

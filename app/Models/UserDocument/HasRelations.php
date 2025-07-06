<?php

namespace App\Models\UserDocument;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasRelations
{
    /**
     * @return BelongsTo
     */
    function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * @return BelongsTo
     */
    function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models\Document;

use App\Models\DocumentRead;
use App\Models\User;
use App\Models\UserDocument;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasRelations
{
    /**
     * @return BelongsTo
     */
    function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany
     */
    function reads(): HasMany
    {
        return $this->hasMany(DocumentRead::class, 'document_id');
    }


    /**
     * @return HasMany
     */
    function userDocuments(): HasMany
    {
        return $this->hasMany(UserDocument::class, 'document_id');
    }
}

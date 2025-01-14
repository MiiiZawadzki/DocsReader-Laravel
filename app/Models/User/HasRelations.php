<?php

namespace App\Models\User;

use App\Models\Document;
use App\Models\DocumentRead;
use App\Models\UserDocument;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasRelations
{
    /**
     * @return HasMany
     */
    function reads(): HasMany
    {
        return $this->hasMany(DocumentRead::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    function userDocuments(): HasMany
    {
        return $this->hasMany(UserDocument::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'user_id');
    }
}

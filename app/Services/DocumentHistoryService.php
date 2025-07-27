<?php

namespace App\Services;

use App\Models\DocumentRead;
use App\Models\User;
use App\Services\Transformers\DocumentsHistoryDataTransformer;
use Illuminate\Support\Collection;

class DocumentHistoryService
{
    /**
     * @param User $user
     * @return Collection
     */
    public function get(User $user): Collection
    {
        return DocumentRead::with('document')
            ->where('user_id', $user->getKey())
            ->get()
            ->map(fn(DocumentRead $documentRead) => DocumentsHistoryDataTransformer::transform($documentRead));
    }
}

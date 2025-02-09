<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use App\Services\Transformers\IndexDocumentsDataTransformer;
use Illuminate\Support\Collection;

class ManageDocumentService
{
    /**
     * @param User $user
     * @return Collection
     */
    public function get(User $user): Collection
    {
        return Document::with(['user', 'reads'])
            ->forManager($user)
            ->get()
            ->map(
                fn(Document $document) => IndexDocumentsDataTransformer::transform($document, $user)
            );
    }
}

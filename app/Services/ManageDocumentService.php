<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use App\Services\Transformers\IndexDocumentsDataTransformer;
use App\Services\Transformers\ManageDocument\ShowDocumentDataTransformer;
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

    /**
     * @param Document $document
     * @param User $user
     * @return array
     */
    public function show(Document $document, User $user): array
    {
        return ShowDocumentDataTransformer::transform($document, $user);
    }
}

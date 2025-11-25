<?php

namespace Modules\History\Services;

use App\Models\DocumentRead;
use App\Services\Transformers\DocumentsHistoryDataTransformer;
use Illuminate\Support\Collection;

class DocumentHistoryService
{
    /**
     * @return Collection
     */
    public function get(): Collection
    {
        // TODO:- use repository
        return collect();
//        return DocumentRead::with('document')
//            ->where('user_id', $user->getKey())
//            ->get()
//            ->map(fn(DocumentRead $documentRead) => DocumentsHistoryDataTransformer::transform($documentRead));
    }
}

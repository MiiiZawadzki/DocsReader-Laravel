<?php

namespace Modules\History\Repositories;

use Illuminate\Support\Collection;
use Modules\History\Models\DocumentRead;
use Modules\History\Repositories\Contracts\DocumentReadRepositoryInterface;

class DocumentReadRepository implements DocumentReadRepositoryInterface
{
    /**
     * @param int $documentId
     * @param int $userId
     * @return DocumentRead
     */
    public function markAsRead(int $documentId, int $userId): DocumentRead
    {
        return DocumentRead::firstOrCreate([
            'document_id' => $documentId,
            'user_id' => $userId,
            'confirmed' => true
        ]);
    }

    /**
     * @param int $userId
     * @return Collection
     */
    public function getReadDocuments(int $userId): Collection
    {
        return DocumentRead::where('user_id', $userId)
            ->get();
    }
}

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

    /**
     * @param int $userId
     * @param array $documentIds
     * @return Collection
     */
    public function getReadStatusForDocuments(int $userId, array $documentIds): Collection
    {
        return DocumentRead::whereIn('document_id', $documentIds)
            ->where('user_id', $userId)
            ->get();
    }

    /**
     * @param int $userId
     * @param int $documentId
     * @return DocumentRead|null
     */
    public function getReadStatusForDocument(int $userId, int $documentId): ?DocumentRead
    {
        return DocumentRead::where('document_id', $documentId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * @param int $documentId
     * @return int
     */
    public function getDocumentReadCount(int $documentId): int
    {
        return DocumentRead::where('document_id', $documentId)->count();
    }

    /**
     * @param int $userId
     * @return int
     */
    public function getUserDocumentReadCount(int $userId): int
    {
        return DocumentRead::where('user_id', $userId)->count();
    }

    /**
     * @param array $documentsId
     * @return int
     */
    public function getDocumentsReadCount(array $documentsId): int
    {
        return DocumentRead::whereIn('document_id', $documentsId)->count();
    }
}

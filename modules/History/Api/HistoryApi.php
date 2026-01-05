<?php

namespace Modules\History\Api;

use Illuminate\Support\Collection;
use Modules\History\DTO\DocumentReadStatusDTO;
use Modules\History\Models\DocumentRead;

class HistoryApi implements HistoryApiInterface
{
    // TODO:- use repository!
    /**
     * @param int $userId
     * @param array $documentIds
     * @return Collection
     */
    public function getReadStatusForDocuments(int $userId, array $documentIds): Collection
    {
        return DocumentRead::whereIn('document_id', $documentIds)->where('user_id', $userId)
            ->get()
            ->map(fn(DocumentRead $documentRead) => DocumentReadStatusDTO::fromModel($documentRead));
    }

    /**
     * @param int $userId
     * @param int $documentId
     * @return DocumentReadStatusDTO
     */
    public function getReadStatusForDocument(int $userId, int $documentId): DocumentReadStatusDTO
    {
        $model = DocumentRead::where('document_id', $documentId)
            ->where('user_id', $userId)
            ->first();

        return DocumentReadStatusDTO::fromModel($model);
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

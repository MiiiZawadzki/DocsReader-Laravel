<?php

namespace Modules\History\Api;

use Illuminate\Support\Collection;
use Modules\History\DTO\DocumentReadStatusDTO;

interface HistoryApiInterface
{
    /**
     * @param int $userId
     * @param array $documentIds
     * @return Collection
     */
    public function getReadStatusForDocuments(int $userId, array $documentIds): Collection;

    /**
     * @param int $userId
     * @param int $documentId
     * @return DocumentReadStatusDTO
     */
    public function getReadStatusForDocument(int $userId, int $documentId): DocumentReadStatusDTO;

    /**
     * @param int $documentId
     * @return int
     */
    public function getDocumentReadCount(int $documentId): int;

    /**
     * @param int $userId
     * @return int
     */
    public function getUserDocumentReadCount(int $userId): int;

    /**
     * @param array $documentsId
     * @return int
     */
    public function getDocumentsReadCount(array $documentsId): int;
}

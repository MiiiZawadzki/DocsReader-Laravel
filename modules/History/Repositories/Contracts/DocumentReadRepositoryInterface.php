<?php

namespace Modules\History\Repositories\Contracts;

use Illuminate\Support\Collection;
use Modules\History\Models\DocumentRead;

interface DocumentReadRepositoryInterface
{
    /**
     * @param int $documentId
     * @param int $userId
     * @return DocumentRead
     */
    public function markAsRead(int $documentId, int $userId): DocumentRead;

    /**
     * @param int $userId
     * @return Collection
     */
    public function getReadDocuments(int $userId): Collection;
}

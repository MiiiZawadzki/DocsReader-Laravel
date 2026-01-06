<?php

namespace Modules\Document\Repositories\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface UserDocumentRepositoryInterface
{
    /**
     * @param int $documentId
     * @return array
     */
    public function getAssignedUserIds(int $documentId): array;

    /**
     * @param int $userId
     * @return Collection
     */
    public function getAssignedDocuments(int $userId): Collection;

    /**
     * @param int $userId
     * @param Carbon $date
     * @return array
     */
    public function getAssignedDocumentsCountForDate(int $userId, Carbon $date): array;

    /**
     * @param array $documentIds
     * @return array
     */
    public function getAssignedForDocuments(array $documentIds): array;

    /**
     * @param int $documentId
     * @param int $userId
     * @param int $createdById
     * @return void
     */
    public function assignUser(int $documentId, int $userId, int $createdById): void;

    /**
     * @param int $documentId
     * @param int $userId
     * @return void
     */
    public function unassignUser(int $documentId, int $userId): void;
}

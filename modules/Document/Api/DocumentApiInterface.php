<?php

namespace Modules\Document\Api;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Document\DTO\DocumentDTO;

interface DocumentApiInterface
{
    /**
     * @param  int  $documentId
     * @return int
     */
    public function getAssignedUsersCount(int $documentId): int;

    /**
     * @param  int  $userId
     * @return int
     */
    public function getAssignedDocumentsCount(int $userId): int;

    /**
     * @param  int  $userId
     * @param  string  $documentUuid
     * @return bool
     */
    public function verifyAssignedDocument(int $userId, string $documentUuid): bool;

    /**
     * @param  int  $userId
     * @param  Carbon  $date
     * @return int
     */
    public function getAssignedDocumentsCountForDate(int $userId, Carbon $date): int;

    /**
     * @param  int  $userId
     * @param  Carbon  $date
     * @return int
     */
    public function getCreatedDocumentsCountForDate(int $userId, Carbon $date): int;

    /**
     * @param  int  $userId
     * @return int
     */
    public function getCreatedDocumentsCount(int $userId): int;

    /**
     * @param  string  $documentUuid
     * @return DocumentDTO|null
     */
    public function getDocumentByUuid(string $documentUuid): ?DocumentDTO;

    /**
     * @param  int  $userId
     * @return Collection<int, DocumentDTO>
     */
    public function getManagerDocuments(int $userId): Collection;

    /**
     * @param  int  $userId
     * @param  string  $documentUuid
     * @return bool
     */
    public function isManagerOf(int $userId, string $documentUuid): bool;

    /**
     * @param  array  $documentsId
     * @return Collection<int, DocumentDTO>
     */
    public function getDocumentsById(array $documentsId): Collection;
}

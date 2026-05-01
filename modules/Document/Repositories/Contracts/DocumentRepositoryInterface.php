<?php

namespace Modules\Document\Repositories\Contracts;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Document\Models\Document;

interface DocumentRepositoryInterface
{
    /**
     * @param  array  $documentData
     * @return Document
     */
    public function create(array $documentData): Document;

    /**
     * @param  string  $documentUuid
     * @return Document
     */
    public function getByUuid(string $documentUuid): Document;

    /**
     * @param  array  $documentsId
     * @return Collection
     */
    public function getDocumentsById(array $documentsId): Collection;

    /**
     * @param  int  $userId
     * @return Collection
     */
    public function getForUser(int $userId): Collection;

    /**
     * @param  int  $userId
     * @return Collection
     */
    public function getForManager(int $userId): Collection;

    /**
     * @param  string  $documentUuid
     * @param  int  $userId
     * @return bool
     */
    public function isManagedBy(string $documentUuid, int $userId): bool;

    /**
     * @param  Document  $document
     * @param  array  $documentData
     * @return Document
     */
    public function update(Document $document, array $documentData): Document;

    /**
     * @param  string  $documentUuid
     * @return bool
     */
    public function delete(string $documentUuid): bool;

    /**
     * @param  int  $userId
     * @param  Carbon  $date
     * @return Collection
     */
    public function getCreatedDocumentsForDate(int $userId, Carbon $date): Collection;
}

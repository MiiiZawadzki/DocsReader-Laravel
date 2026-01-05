<?php

namespace Modules\Document\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Document\Models\Document;
use Modules\Document\Models\UserDocument;
use Modules\Document\Repositories\Contracts\DocumentRepositoryInterface;

class DocumentRepository implements DocumentRepositoryInterface
{
    /**
     * @param array $documentData
     * @return Document
     */
    public function create(array $documentData): Document
    {
        return Document::create($documentData);
    }

    /**
     * @param int $userId
     * @return Collection
     */
    public function getForUser(int $userId): Collection
    {
        $userDocumentIds = UserDocument::where('user_id', $userId)->pluck('document_id');
        $date = Carbon::today();

        return Document::whereIn('id', $userDocumentIds)
            ->where(function (Builder $builder) use ($date) {
                $builder->whereDate('date_from', '<=', $date);
                $builder->whereNull('date_to')
                    ->orWhereDate('date_to', '>=', $date);
            })
            ->get();
    }

    /**
     * @param int $userId
     * @return Collection
     */
    public function getForManager(int $userId): Collection
    {
        return Document::where('user_id', $userId)->get();
    }

    /**
     * @param string $documentUuid
     * @return Document
     */
    public function getByUuid(string $documentUuid): Document
    {
        return Document::where('uuid', $documentUuid)->first();
    }

    /**
     * @param Document $document
     * @param array $documentData
     * @return Document
     */
    public function update(Document $document, array $documentData): Document
    {
        $document->update($documentData);
        return $document->fresh();
    }

    /**
     * @param string $documentUuid
     * @return bool
     */
    public function delete(string $documentUuid): bool
    {
        return Document::where('uuid', $documentUuid)->delete();
    }

    /**
     * @param array $documentsId
     * @return Collection
     */
    public function getDocumentsById(array $documentsId): Collection
    {
        return Document::whereIn('id', $documentsId)->get();
    }

    /**
     * @param int $userId
     * @param Carbon $date
     * @return Collection
     */
    public function getCreatedDocumentsForDate(int $userId, Carbon $date): Collection
    {
        return Document::where('user_id', $userId)
            ->where(function (Builder $builder) use ($date) {
                $builder->whereDate('date_from', '<=', $date);
                $builder->whereNull('date_to')
                    ->orWhereDate('date_to', '>=', $date);
            })
            ->get();
    }
}

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
     * @param  array  $documentData
     * @return Document
     */
    public function create(array $documentData): Document
    {
        return Document::create($documentData);
    }

    /**
     * @param  int  $userId
     * @param  string|null  $query
     * @return Collection
     */
    public function getForUser(int $userId, ?string $query = null): Collection
    {
        $userDocumentIds = UserDocument::where('user_id', $userId)->pluck('document_id');
        $date = Carbon::today();

        return Document::whereIn('id', $userDocumentIds)
            ->where(function (Builder $builder) use ($date) {
                $builder->whereDate('date_from', '<=', $date);
                $builder->whereNull('date_to')
                    ->orWhereDate('date_to', '>=', $date);
            })
            ->when($query !== null && $query !== '', function (Builder $builder) use ($query) {
                $builder->where(function (Builder $inner) use ($query) {
                    $inner->where('name', 'like', '%' . $query . '%')
                        ->orWhere('description', 'like', '%' . $query . '%');
                });
            })
            ->get();
    }

    /**
     * @param  int  $userId
     * @param  string|null  $query
     * @return Collection
     */
    public function getForManager(int $userId, ?string $query = null): Collection
    {
        return Document::where('user_id', $userId)
            ->when($query !== null && $query !== '', function (Builder $builder) use ($query) {
                $builder->where(function (Builder $inner) use ($query) {
                    $inner->where('name', 'like', '%' . $query . '%')
                        ->orWhere('description', 'like', '%' . $query . '%');
                });
            })
            ->get();
    }

    /**
     * @param  string  $documentUuid
     * @param  int  $userId
     * @return bool
     */
    public function isManagedBy(string $documentUuid, int $userId): bool
    {
        return Document::where('uuid', $documentUuid)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * @param  string  $documentUuid
     * @return Document|null
     */
    public function getByUuid(string $documentUuid): ?Document
    {
        return Document::where('uuid', $documentUuid)->first();
    }

    /**
     * @param  Document  $document
     * @param  array  $documentData
     * @return Document
     */
    public function update(Document $document, array $documentData): Document
    {
        $document->update($documentData);

        return $document->fresh();
    }

    /**
     * @param  string  $documentUuid
     * @return bool
     */
    public function delete(string $documentUuid): bool
    {
        return Document::where('uuid', $documentUuid)->delete();
    }

    /**
     * @param  array  $documentsId
     * @param  string|null  $query
     * @return Collection
     */
    public function getDocumentsById(array $documentsId, ?string $query = null): Collection
    {
        return Document::whereIn('id', $documentsId)
            ->when($query !== null && $query !== '', function (Builder $builder) use ($query) {
                $builder->where(function (Builder $inner) use ($query) {
                    $inner->where('name', 'like', '%' . $query . '%')
                        ->orWhere('description', 'like', '%' . $query . '%');
                });
            })
            ->get();
    }

    /**
     * @param  int  $userId
     * @param  Carbon  $date
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

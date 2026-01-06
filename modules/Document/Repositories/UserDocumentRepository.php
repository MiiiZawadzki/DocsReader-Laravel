<?php

namespace Modules\Document\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Document\Models\UserDocument;
use Modules\Document\Repositories\Contracts\UserDocumentRepositoryInterface;

class UserDocumentRepository implements UserDocumentRepositoryInterface
{
    /**
     * @param int $documentId
     * @return array
     */
    public function getAssignedUserIds(int $documentId): array
    {
        return UserDocument::where('document_id', $documentId)
            ->whereHas('document')
            ->pluck('user_id')
            ->toArray();
    }

    /**
     * @param int $userId
     * @return Collection
     */
    public function getAssignedDocuments(int $userId): Collection
    {
        return UserDocument::where('user_id', $userId)
            ->whereHas('document')
            ->get();
    }

    /**
     * @param int $userId
     * @param Carbon $date
     * @return array
     */
    public function getAssignedDocumentsCountForDate(int $userId, Carbon $date): array
    {
        return UserDocument::where('user_id', $userId)
            ->whereHas('document', function (Builder $builder) use ($date) {
                $builder->whereDate('date_from', '<=', $date);
                $builder->whereNull('date_to')
                    ->orWhereDate('date_to', '>=', $date);
            })
            ->pluck('document_id')
            ->toArray();
    }

    /**
     * @param array $documentIds
     * @return array
     */
    public function getAssignedForDocuments(array $documentIds): array
    {
        return UserDocument::whereIn('document_id', $documentIds)
            ->whereHas('document')
            ->pluck('document_id')
            ->toArray();
    }

    /**
     * @param int $documentId
     * @param int $userId
     * @param int $createdById
     * @return void
     */
    public function assignUser(int $documentId, int $userId, int $createdById): void
    {
        UserDocument::firstOrCreate(
            [
                'user_id' => $userId,
                'document_id' => $documentId,
            ],
            ['created_by' => $createdById]
        );
    }

    /**
     * @param int $documentId
     * @param int $userId
     * @return void
     */
    public function unassignUser(int $documentId, int $userId): void
    {
        UserDocument::where([
            'user_id' => $userId,
            'document_id' => $documentId,
        ])->delete();
    }
}

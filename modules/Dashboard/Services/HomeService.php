<?php

namespace Modules\Dashboard\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Document\Api\DocumentApiInterface;
use Modules\History\Api\HistoryApiInterface;

class HomeService
{
    public function __construct(
        private readonly HistoryApiInterface  $historyApi,
        private readonly DocumentApiInterface $documentApi
    )
    {
    }

    /**
     * @param int $userId
     * @param Carbon $date
     * @return Collection
     */
    public function data(int $userId, Carbon $date): Collection
    {
        $totalActiveDocuments = $this->documentApi->getAssignedDocumentsCountForDate($userId, $date);
        $readDocuments = $this->historyApi->getUserDocumentReadCount($userId);

        return collect([
            'total' => $totalActiveDocuments,
            'read' => $readDocuments,
        ]);
    }
}

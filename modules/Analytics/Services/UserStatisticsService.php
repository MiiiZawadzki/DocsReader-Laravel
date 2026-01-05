<?php

namespace Modules\Analytics\Services;

use Carbon\Carbon;
use Modules\Document\Api\DocumentApiInterface;
use Modules\History\Api\HistoryApiInterface;

class UserStatisticsService
{
    public function __construct(
        private readonly HistoryApiInterface $historyApi,
        private readonly DocumentApiInterface $documentApi
    )
    {
    }

    /**
     * @param int $userId
     * @return array[]
     */
    public function readStatistics(int $userId): array
    {
        $totalDocuments = $this->totalDocuments($userId);
        $documentsRead = $this->readDocuments($userId);

        $notRead = max($totalDocuments - $documentsRead, 0);

        $readPercentage = round(
            ($documentsRead / max($totalDocuments, 1)) * 100,
            2
        );
        $notReadPercentage = round(($notRead / max($totalDocuments, 1)) * 100, 2);

        return [
            [
                'value' => $documentsRead,
                'name' => __("analytics::messages.charts.read", ["percentage" => $readPercentage])
            ],
            [
                'value' => $notRead,
                'name' => __("analytics::messages.charts.not_read", ["percentage" => $notReadPercentage])
            ],
        ];
    }

    /**
     * @param int $userId
     * @param Carbon $date
     * @return int
     */
    public function activeDocuments(int $userId, Carbon $date): int
    {
        return $this->documentApi->getAssignedDocumentsCountForDate($userId, $date);
    }

    /**
     * @param int $userId
     * @return int
     */
    public function totalDocuments(int $userId): int
    {
        return $this->documentApi->getAssignedDocumentsCount($userId);
    }

    /**
     * @param int $userId
     * @return int
     */
    public function readDocuments(int $userId): int
    {
        return $this->historyApi->getUserDocumentReadCount($userId);
    }
}

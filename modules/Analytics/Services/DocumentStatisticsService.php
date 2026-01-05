<?php

namespace Modules\Analytics\Services;

use Modules\Document\Api\DocumentApiInterface;
use Modules\History\Api\HistoryApiInterface;

class DocumentStatisticsService
{
    public function __construct(
        private readonly HistoryApiInterface $historyApi,
        private readonly DocumentApiInterface $documentApi
    )
    {
    }
    /**
     * @param int $documentId
     * @return array[]
     */
    public function readStatistics(int $documentId): array
    {
        $totalAssigned = $this->documentAssignment($documentId);
        $totalRead = $this->documentReads($documentId);
        $totalNotRead = $totalAssigned - $totalRead;

        $readPercentage = round(
            ($totalRead / max($totalAssigned, 1)) * 100,
            2
        );
        $notReadPercentage = round(($totalNotRead / max($totalAssigned, 1)) * 100, 2);

        return [
            [
                'value' => $totalRead,
                'name' => __("analytics::messages.charts.read", ["percentage" => $readPercentage])
            ],
            [
                'value' => $totalNotRead,
                'name' => __("analytics::messages.charts.not_read", ["percentage" => $notReadPercentage])
            ],
        ];
    }

    /**
     * @param int $documentId
     * @return int
     */
    public function documentReads(int $documentId): int
    {
        return $this->historyApi->getDocumentReadCount($documentId);
    }

    /**
     * @param int $documentId
     * @return int
     */
    public function documentAssignment(int $documentId): int
    {
        return $this->documentApi->getAssignedUsersCount($documentId);
    }

    /**
     * @param int $documentId
     * @return float
     */
    public function documentReadRatio(int $documentId): float
    {
        $totalAssigned = $this->documentAssignment($documentId);
        $totalRead = $this->documentReads($documentId);

        return round(
            ($totalRead / max($totalAssigned, 1)),
            2
        );
    }
}

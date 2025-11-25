<?php

namespace Modules\Analytics\Services;

class DocumentStatisticsService
{
    /**
     * @return array[]
     */
    public function readStatistics(): array
    {
//        $totalAssigned = $this->documentAssignment($document);
//        $totalRead = $this->documentReads($document);
//        $totalNotRead = $totalAssigned - $totalRead;
//
//        $readPercentage = round(
//            ($totalRead / max($totalAssigned, 1)) * 100,
//            2
//        );
//        $notReadPercentage = round(($totalNotRead / max($totalAssigned, 1)) * 100, 2);
//
//        return [
//            [
//                'value' => $totalRead,
//                'name' => "Read ($readPercentage %)"
//            ],
//            [
//                'value' => $totalNotRead,
//                'name' => "Not read ($notReadPercentage %)"
//            ],
//        ];
        return [
            [
                'value' => 12,
                'name' => "Read (23 %)"
            ],
            [
                'value' => 33,
                'name' => "Not read (12 %)"
            ],
        ];
    }

    /**
     * @return int
     */
    public function documentReads(): int
    {
        return 12;
//        return DocumentRead::where('document_id', $document->getKey())->count();
    }

    /**
     * @return int
     */
    public function documentAssignment(): int
    {
        return 7;
//        return UserDocument::where('document_id', $document->getKey())->count();
    }

    /**
     * @return float
     */
    public function documentReadRatio(): float
    {
        return 3.14;
//        $totalAssigned = $this->documentAssignment($document);
//        $totalRead = $this->documentReads($document);
//
//        return round(
//            ($totalRead / max($totalAssigned, 1)),
//            2
//        );
    }
}

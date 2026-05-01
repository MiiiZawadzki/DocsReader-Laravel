<?php

namespace Modules\Document\Transformers;

use Carbon\Carbon;
use Modules\Document\Models\Document;

class ShowDocumentDataTransformer
{
    /**
     * @param Document $document
     * @param Carbon|null $readDate
     * @param string|null $authorTag
     * @return array
     */
    public static function transform(Document $document, ?Carbon $readDate, ?string $authorTag): array
    {
        return [
            "id" => $document->getAttribute('uuid'),
            "name" => $document->getAttribute('name'),
            "description" => $document->getAttribute('description'),
            "declaration" => $document->getAttribute('declaration_message'),
            "hasDeclaration" => !empty($document->getAttribute('declaration_message')),
            "delay" => $document->getAttribute('delay'),
            "status" => self::getStatus($readDate),
            "userTag" => $authorTag ?? "-",
            "authorId" => $document->getAttribute('user_id'),
            "dateTag" => $document->getAttribute('date_from')->format('Y-m-d'),
            "dateFrom" => $document->getAttribute('date_from')->format('Y-m-d'),
            "dateTo" => $document->getAttribute('date_to')?->format('Y-m-d') ?? '',
            "isRead" => $readDate !== null,
            "readDate" => $readDate?->format('Y-m-d'),
            "fileUrl" => route('getFile', ['document' => $document->getAttribute('uuid')]),
        ];
    }

    /**
     * @param Carbon|null $readDate
     * @return array
     */
    protected static function getStatus(?Carbon $readDate): array
    {
        if (isset($readDate)) {
            return [
                'read' => true,
                'name' => __('document::messages.statuses.read'),
                'date' => $readDate->format('Y-m-d'),
            ];
        }

        return [
            "read" => false,
            "name" => __('document::messages.statuses.new'),
            "date" => null
        ];
    }
}

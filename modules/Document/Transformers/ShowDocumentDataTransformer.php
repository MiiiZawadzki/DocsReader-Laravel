<?php

namespace Modules\Document\Transformers;

use Carbon\Carbon;
use Modules\Document\Models\Document;

class ShowDocumentDataTransformer
{
    /**
     * @param  Document  $document
     * @param  Carbon|null  $readDate
     * @param  string|null  $authorTag
     * @param  string|null  $certificateId
     * @return array
     */
    public static function transform(
        Document $document,
        ?Carbon $readDate,
        ?string $authorTag,
        ?string $certificateId = null,
    ): array {
        return [
            'id' => $document->getAttribute('uuid'),
            'name' => $document->getAttribute('name'),
            'description' => $document->getAttribute('description'),
            'declaration' => $document->getAttribute('declaration_message'),
            'requiresConfirmation' => $document->getAttribute('requires_confirmation') ?? false,
            'delay' => $document->getAttribute('delay'),
            'status' => self::getStatus($readDate),
            'userTag' => $authorTag ?? '-',
            'authorId' => $document->getAttribute('user_id'),
            'dateTag' => $document->getAttribute('date_from')->format('Y-m-d'),
            'dateFrom' => $document->getAttribute('date_from')->format('Y-m-d'),
            'dateTo' => $document->getAttribute('date_to')?->format('Y-m-d') ?? '',
            'isRead' => $readDate !== null,
            'readDate' => $readDate?->format('Y-m-d'),
            'certificateId' => $certificateId,
            'fileUrl' => route('getFile', ['document' => $document->getAttribute('uuid')]),
        ];
    }

    /**
     * @param  Carbon|null  $readDate
     * @return array
     */
    protected static function getStatus(?Carbon $readDate): array
    {
        if (isset($readDate)) {
            return [
                'read' => true,
                'name' => __('common::messages.statuses.read'),
                'date' => $readDate->format('Y-m-d'),
            ];
        }

        return [
            'read' => false,
            'name' => __('common::messages.statuses.new'),
            'date' => null,
        ];
    }
}

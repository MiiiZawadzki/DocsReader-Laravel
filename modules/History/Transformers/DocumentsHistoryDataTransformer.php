<?php

namespace Modules\History\Transformers;


use Modules\History\Models\DocumentRead;

class DocumentsHistoryDataTransformer
{
    /**
     * @param DocumentRead $documentRead
     * @return array
     */
    public static function transform(DocumentRead $documentRead): array
    {
        $document = $documentRead->document;
        return [
            "id" => $document->getAttribute('uuid'),
            "name" => $document->getAttribute('name'),
            "description" => $document->getAttribute('description'),
            "status" => self::getStatus($documentRead),
            "userTag" => $document->user->name,
            "dateTag" => $document->getAttribute('date_from')->format('Y-m-d'),
            "buttonText" => "Go to document"
        ];
    }

    /**
     * @param DocumentRead $documentRead
     * @return array
     */
    protected static function getStatus(DocumentRead $documentRead): array
    {
        return [
            'read' => true,
            'name' => __('api.document.statuses.read'),
            'date' => $documentRead->getAttribute('created_at')->format('Y-m-d'),
        ];
    }
}

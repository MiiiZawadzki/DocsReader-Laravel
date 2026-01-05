<?php

namespace Modules\Document\Transformers;

use Illuminate\Support\Collection;
use Modules\Document\Models\Document;

class IndexDocumentsDataTransformer extends AbstractDocumentsDataTransformer
{
    /**
     * @param Document $document
     * @param Collection $readStatuses
     * @param Collection $authorTags
     * @return array
     */
    public static function transform(Document $document, Collection $readStatuses, Collection $authorTags): array
    {
        return [
            "id" => $document->getAttribute('uuid'),
            "name" => $document->getAttribute('name'),
            "description" => $document->getAttribute('description'),
            "status" => self::getStatus($document, $readStatuses),
            "userTag" => self::getAuthorTag($document, $authorTags),
            "dateTag" => $document->getAttribute('date_from')->format('Y-m-d'),
            "buttonText" => "Go to document"
        ];
    }
}

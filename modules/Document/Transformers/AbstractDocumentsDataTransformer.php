<?php

namespace Modules\Document\Transformers;

use Illuminate\Support\Collection;
use Modules\Document\Models\Document;

class AbstractDocumentsDataTransformer
{
    /**
     * @param Document $document
     * @param Collection $readStatuses
     * @return array
     */
    protected static function getStatus(Document $document, Collection $readStatuses): array
    {
        $read = $readStatuses->where('documentId', $document->getKey())->first();

        if ($read) {
            return [
                'read' => true,
                'name' => __('common::messages.statuses.read'),
                'date' => $read->createdAt->format('Y-m-d'),
            ];
        }

        return [
            "read" => false,
            "name" => __('common::messages.statuses.new'),
            "date" => null
        ];
    }

    /**
     * @param Document $document
     * @param Collection $authorTags
     * @return string
     */
    protected static function getAuthorTag(Document $document, Collection $authorTags): string
    {
        $author = $authorTags->get($document->getAttribute('user_id'));

        return $author ?? "-";
    }
}

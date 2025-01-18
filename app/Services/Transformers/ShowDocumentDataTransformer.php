<?php

namespace App\Services\Transformers;

use App\Models\Document;
use App\Models\User;

class ShowDocumentDataTransformer extends IndexDocumentsDataTransformer
{
    /**
     * @param Document $document
     * @param User $user
     * @return array
     */
    public static function transform(Document $document, User $user): array
    {
        return [
            "id" => $document->getAttribute('uuid'),
            "name" => $document->getAttribute('name'),
            "description" => $document->getAttribute('description'),
            "declaration" => $document->getAttribute('declaration_message'),
            "hasDeclaration" => !empty($document->getAttribute('declaration_message')),
            "delay" => $document->getAttribute('delay'),
            "status" => self::getStatus($document, $user),
            "userTag" => $document->user->name,
            "dateTag" => $document->getAttribute('date_from')->format('Y-m-d'),
            "fileUrl" => route('getFile', ['document' => $document->getAttribute('uuid')]),
        ];
    }
}

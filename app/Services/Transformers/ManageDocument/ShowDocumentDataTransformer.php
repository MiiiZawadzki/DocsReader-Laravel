<?php

namespace App\Services\Transformers\ManageDocument;

use App\Models\Document;
use App\Models\User;
use App\Services\Transformers\IndexDocumentsDataTransformer;
use Illuminate\Support\Facades\Storage;

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
            "dateFrom" => $document->getAttribute('date_from')->format('Y-m-d'),
            "dateTo" => $document->getAttribute('date_to')?->format('Y-m-d') ?? '',
            "fileName" => $document->getAttribute('source_name'),
            "fileSize" => Storage::disk('documents')->size($document->getAttribute('file_path')) / (1024 * 1024),
            "fileUrl" => route('getFile', ['document' => $document->getAttribute('uuid')]),
        ];
    }
}

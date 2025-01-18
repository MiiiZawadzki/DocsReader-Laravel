<?php

namespace App\Services\Transformers;

use App\Models\Document;
use App\Models\DocumentRead;
use App\Models\User;

class IndexDocumentsDataTransformer
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
            "status" => self::getStatus($document, $user),
            "userTag" => $document->user->name,
            "dateTag" => $document->getAttribute('date_from')->format('Y-m-d'),
            "buttonText" => "Go to document"
        ];
    }

    /**
     * @param Document $document
     * @param User $user
     * @return array
     */
    protected static function getStatus(Document $document, User $user): array
    {
        $read = $document
            ->reads
            ->filter(fn(DocumentRead $read) => $read->user_id === $user->getKey())
            ->map(fn(DocumentRead $read) => [
                'read' => true,
                'name' => __('api.document.statuses.read'),
                'date' => $read->getAttribute('created_at')->format('Y-m-d'),
            ])->first();

        if ($read === null) {
            $read = [
                "read" => false,
                "name" => __('api.document.statuses.new'),
                "date" => null
            ];
        }
        return $read;
    }
}

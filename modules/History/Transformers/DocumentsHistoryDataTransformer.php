<?php

namespace Modules\History\Transformers;


use Illuminate\Support\Collection;
use Modules\Document\DTO\DocumentDTO;
use Modules\History\Models\DocumentRead;

class DocumentsHistoryDataTransformer
{
    /**
     * @param DocumentDTO $documentDto
     * @param Collection $readStatuses
     * @param Collection $authorTags
     * @return array
     */
    public static function transform(DocumentDTO $documentDto, Collection $readStatuses, Collection $authorTags): array
    {
        return [
            "id" => $documentDto->uuid,
            "name" => $documentDto->name,
            "description" => $documentDto->description,
            "status" => self::getStatus($documentDto->id, $readStatuses),
            "userTag" => self::getAuthorTag($documentDto->userId, $authorTags),
            "dateTag" => $documentDto->dateFrom->format('Y-m-d'),
            "buttonText" => "Go to document"
        ];
    }

    /**
     * @param int $documentId
     * @param Collection $readStatuses
     * @return array
     */
    protected static function getStatus(int $documentId, Collection $readStatuses): array
    {
        $read = $readStatuses->where('documentId', $documentId)->first();

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
     * @param int $userId
     * @param Collection $authorTags
     * @return string
     */
    protected static function getAuthorTag(int $userId, Collection $authorTags): string
    {
        $author = $authorTags->get($userId);

        return $author ?? "-";
    }
}

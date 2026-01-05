<?php

namespace Modules\Document\DTO;

use Carbon\Carbon;
use Modules\Document\Models\Document;

readonly class DocumentDTO
{
    /**
     * @param int $id
     * @param string $name
     * @param string $uuid
     * @param string $sourceName
     * @param string $description
     * @param int $userId
     * @param string $filePath
     * @param Carbon $dateFrom
     * @param Carbon|null $dateTo
     * @param string $declarationMessage
     * @param int $delay
     */
    public function __construct(
        public int    $id,
        public string $name,
        public string $uuid,
        public string $sourceName,
        public string $description,
        public int    $userId,
        public string $filePath,
        public Carbon $dateFrom,
        public ?Carbon $dateTo,
        public string $declarationMessage,
        public int    $delay,
    )
    {
    }

    public static function fromModel(Document $document): self
    {
        return new self(
            id: $document->getAttribute('id'),
            name: $document->getAttribute('name'),
            uuid: $document->getAttribute('name'),
            sourceName: $document->getAttribute('source_name'),
            description: $document->getAttribute('description'),
            userId: $document->getAttribute('user_id'),
            filePath: $document->getAttribute('file_path'),
            dateFrom: $document->getAttribute('date_from'),
            dateTo: $document->getAttribute('date_to'),
            declarationMessage: $document->getAttribute('declaration_message'),
            delay: $document->getAttribute('delay'),
        );
    }
}

<?php

namespace Modules\History\DTO;

use Carbon\Carbon;
use Modules\History\Models\DocumentRead;

readonly class DocumentReadStatusDTO
{
    public function __construct(
        public ?int     $documentId,
        public ?Carbon $createdAt,
        public ?int     $userId
    )
    {
    }

    public static function fromModel(?DocumentRead $documentRead): self
    {
        return new self(
            documentId: $documentRead?->getAttribute('document_id'),
            createdAt: $documentRead?->getAttribute('created_at'),
            userId: $documentRead?->getAttribute('user_id')
        );
    }
}

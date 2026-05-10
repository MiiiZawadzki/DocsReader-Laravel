<?php

namespace Modules\Document\DTO;

use Carbon\Carbon;
use Modules\Document\Models\Document;

readonly class DocumentListItemDTO
{
    public function __construct(
        public Document $document,
        public ?Carbon $readAt,
        public string $authorTag,
    ) {
    }
}

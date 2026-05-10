<?php

namespace Modules\History\DTO;

use Carbon\Carbon;
use Modules\Document\DTO\DocumentDTO;

readonly class HistoryListItemDTO
{
    public function __construct(
        public DocumentDTO $document,
        public ?Carbon $readAt,
        public string $authorTag,
    ) {
    }
}

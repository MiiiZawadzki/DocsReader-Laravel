<?php

namespace Modules\Document\DTO;

use Modules\Document\Enums\DocumentReadStatus;

readonly class DocumentListFiltersDTO
{
    public function __construct(
        public ?DocumentReadStatus $status = null,
        public ?string $query = null,
    ) {
    }
}

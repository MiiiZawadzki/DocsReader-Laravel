<?php

namespace Modules\History\DTO;

readonly class HistoryListFiltersDTO
{
    public function __construct(
        public ?string $query = null,
    ) {
    }
}

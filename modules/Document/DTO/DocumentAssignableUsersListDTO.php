<?php

namespace Modules\Document\DTO;

use Illuminate\Support\Collection;

readonly class DocumentAssignableUsersListDTO
{
    /**
     * @param  Collection<int, DocumentAssignableUserDTO>  $items
     * @param  int  $total
     * @param  int  $assignedCount
     */
    public function __construct(
        public Collection $items,
        public int $total,
        public int $assignedCount,
    ) {
    }
}

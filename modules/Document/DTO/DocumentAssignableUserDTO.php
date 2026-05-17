<?php

namespace Modules\Document\DTO;

use Modules\User\DTO\UserDTO;

readonly class DocumentAssignableUserDTO
{
    public function __construct(
        public UserDTO $user,
        public bool $assigned,
    ) {
    }
}

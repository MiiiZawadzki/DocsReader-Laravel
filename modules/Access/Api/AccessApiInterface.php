<?php

namespace Modules\Access\Api;

interface AccessApiInterface
{
    /**
     * @param int $userId
     * @return array
     */
    public function getPermissionsForUser(int $userId): array;
}

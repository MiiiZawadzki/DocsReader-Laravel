<?php

namespace Modules\Access\Repository\Contracts;

interface AccessRepositoryInterface
{
    /**
     * @param int $userId
     * @return mixed
     */
    public function getForUserId(int $userId): array;

    /**
     * @param array $permissionsId
     * @return array
     */
    public function getPermissions(array $permissionsId): array;
}

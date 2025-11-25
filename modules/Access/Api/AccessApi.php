<?php

namespace Modules\Access\Api;


use Modules\Access\Repository\Contracts\AccessRepositoryInterface;

readonly class AccessApi implements AccessApiInterface
{
    public function __construct(private AccessRepositoryInterface $repository)
    {
    }

    public function getPermissionsForUser(int $userId): array
    {
        $userPermissions = $this->repository->getForUserId($userId);
        $permissions = $this->repository->getPermissions(
            array_map(fn(array $data) => $data['permission_id'], $userPermissions)
        );

        return array_map(
            fn(array $data) => $data['type'],
            $permissions
        );
    }
}

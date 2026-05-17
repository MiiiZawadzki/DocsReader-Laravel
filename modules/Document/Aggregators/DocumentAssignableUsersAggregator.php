<?php

namespace Modules\Document\Aggregators;

use Modules\Document\DTO\DocumentAssignableUserDTO;
use Modules\Document\DTO\DocumentAssignableUsersListDTO;
use Modules\Document\Models\Document;
use Modules\Document\Repositories\Contracts\UserDocumentRepositoryInterface;
use Modules\User\Api\UserApiInterface;
use Modules\User\DTO\UserDTO;

readonly class DocumentAssignableUsersAggregator
{
    public function __construct(
        private UserApiInterface $userApi,
        private UserDocumentRepositoryInterface $userDocumentRepository,
    ) {
    }

    public function getForDocument(
        Document $document,
        ?string $query = null,
        ?string $filter = null,
    ): DocumentAssignableUsersListDTO {
        $assignedUserIds = $this->userDocumentRepository->getAssignedUserIds($document->getKey());
        $allUsers = $this->userApi->getAllUsers();

        $allItems = $allUsers->map(fn(UserDTO $user) => new DocumentAssignableUserDTO(
            user: $user,
            assigned: in_array($user->id, $assignedUserIds, true),
        ));

        $normalizedQuery = $query !== null ? trim($query) : '';

        $filtered = $allItems
            ->when($normalizedQuery !== '', fn($items) => $items->filter(
                fn(DocumentAssignableUserDTO $item) => mb_stripos($item->user->name, $normalizedQuery) !== false,
            ))
            ->when($filter === 'assigned', fn($items) => $items->filter(
                fn(DocumentAssignableUserDTO $item) => $item->assigned,
            ))
            ->when($filter === 'unassigned', fn($items) => $items->filter(
                fn(DocumentAssignableUserDTO $item) => !$item->assigned,
            ))
            ->values();

        return new DocumentAssignableUsersListDTO(
            items: $filtered,
            total: $allItems->count(),
            assignedCount: count($assignedUserIds),
        );
    }
}

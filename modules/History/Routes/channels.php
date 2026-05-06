<?php

use Illuminate\Support\Facades\Broadcast;
use Modules\Access\Api\AccessApiInterface;
use Modules\Document\Api\DocumentApiInterface;
use Modules\User\Models\User;

Broadcast::channel('history.documents.{documentUuid}', function (User $user, string $documentUuid) {
    $userId = $user->getKey();

    return app(AccessApiInterface::class)->hasPermission($userId, 'manage-documents')
        && app(DocumentApiInterface::class)->isManagerOf($userId, $documentUuid);
});

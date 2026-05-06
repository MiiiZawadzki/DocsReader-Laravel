<?php

use Illuminate\Support\Facades\Broadcast;
use Modules\User\Models\User;

Broadcast::channel('users.{userId}', function (User $user, int $userId) {
    return (int) $user->getKey() === $userId;
});

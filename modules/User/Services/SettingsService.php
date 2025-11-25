<?php

namespace Modules\User\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Modules\User\Models\User;

class SettingsService
{
    /**
     * @param User $user
     * @return Collection
     */
    public function data(User $user): Collection
    {
        return collect([
            'name' => $user->name,
            'email' => $user->email,
            'date' => $user->created_at->format('Y-m-d')
        ]);
    }

    /**
     * @param User $user
     * @param string $name
     * @return bool
     */
    public function updateName(User $user, string $name): bool
    {
        $user->name = $name;
        return $user->save();
    }

    /**
     * @param User $user
     * @param string $email
     * @return bool
     */
    public function updateEmail(User $user, string $email): bool
    {
        $user->email = $email;
        return $user->save();
    }

    /**
     * @param User $user
     * @param string $password
     * @return bool
     */
    public function updatePassword(User $user, string $password): bool
    {
        $user->password = Hash::make($password);
        return $user->save();
    }
}

<?php

namespace App\Policies;

use App\Models\Log;
use App\Models\User;

class LogPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission(User::PERMISSIONS['logs']['view']);
    }

    public function view(User $user, Log $log): bool
    {
        return $user->hasPermission(User::PERMISSIONS['logs']['view']);
    }

    public function delete(User $user, Log $log): bool
    {
        return $user->hasPermission(User::PERMISSIONS['logs']['delete']);
    }
}


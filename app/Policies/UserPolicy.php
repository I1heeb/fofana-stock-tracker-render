<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        // Only admins can access user management (admin panel)
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        // Only admins can view user list
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        // Super admin can update anyone
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Regular admin can update packaging agents and service clients only
        if ($user->isAdmin() && !$model->isAdmin()) {
            return true;
        }
        
        return false;
    }

    public function delete(User $user, User $model): bool
    {
        // Cannot delete yourself
        if ($user->id === $model->id) {
            return false;
        }
        
        // Cannot delete super admin
        if ($model->isSuperAdmin()) {
            return false;
        }
        
        // Super admin can delete anyone (except super admins and themselves)
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Regular admin can only delete packaging agents and service clients
        if ($user->isAdmin() && !$model->isAdmin()) {
            return true;
        }
        
        return false;
    }
}






<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
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
        return $user->hasPermission(User::PERMISSIONS['products']['view']);
    }

    public function view(User $user, Product $product): bool
    {
        return $user->hasPermission(User::PERMISSIONS['products']['view']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(User::PERMISSIONS['products']['create']);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasPermission(User::PERMISSIONS['products']['update']);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasPermission(User::PERMISSIONS['products']['delete']);
    }

    public function manageStock(User $user): bool
    {
        return $user->hasPermission(User::PERMISSIONS['products']['manage_stock']);
    }
}

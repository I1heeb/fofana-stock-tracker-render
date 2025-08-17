<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
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
        return $user->hasPermission(User::PERMISSIONS['orders']['view']);
    }

    public function view(User $user, Order $order): bool
    {
        return $user->hasPermission(User::PERMISSIONS['orders']['view']);
    }

    public function create(User $user): bool
    {
        // Packaging agents and admins can create orders
        return $user->isAdmin() || $user->isPackagingAgent();
    }

    public function update(User $user, Order $order): bool
    {
        // Packaging agents and admins can update orders
        return $user->isAdmin() || $user->isPackagingAgent();
    }
    

    public function delete(User $user, Order $order): bool
    {
        return $user->hasPermission(User::PERMISSIONS['orders']['delete']);
    }

    public function updateStatus(User $user, Order $order): bool
    {
        // Packaging agents and admins can update order status
        return $user->isAdmin() || $user->isPackagingAgent();
    }

    public function bulkOperations(User $user): bool
    {
        return $user->hasPermission(User::PERMISSIONS['orders']['bulk_operations']);
    }
}



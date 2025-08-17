<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
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
        // Tous les utilisateurs authentifiés peuvent voir la liste des fournisseurs
        return true;
    }

    public function view(User $user, Supplier $supplier): bool
    {
        // Tous les utilisateurs authentifiés peuvent voir un fournisseur
        return true;
    }

    public function create(User $user): bool
    {
        // Seuls admin et packaging agent peuvent créer des fournisseurs
        return $user->isAdmin() || $user->role === 'packaging_agent';
    }

    public function update(User $user, Supplier $supplier): bool
    {
        // Seuls admin et packaging agent peuvent modifier des fournisseurs
        return $user->isAdmin() || $user->role === 'packaging_agent';
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        // Seuls admin et packaging agent peuvent supprimer des fournisseurs
        return $user->isAdmin() || $user->role === 'packaging_agent';
    }
}

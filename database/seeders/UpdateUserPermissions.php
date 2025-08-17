<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UpdateUserPermissions extends Seeder
{
    public function run(): void
    {
        // Mettre à jour tous les utilisateurs packaging
        User::where('role', User::ROLE_PACKAGING_AGENT)->each(function ($user) {
            $user->update([
                'permissions' => User::getDefaultPermissions(User::ROLE_PACKAGING_AGENT)
            ]);
        });
    }
}
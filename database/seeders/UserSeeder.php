<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'packaging@example.com'],
            [
                'name' => 'Packaging User',
                'password' => Hash::make('password'),
                'role' => User::ROLE_PACKAGING_AGENT,
                'permissions' => User::getDefaultPermissions(User::ROLE_PACKAGING_AGENT),
            ]
        );

        User::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Service Client',
                'password' => Hash::make('password'),
                'role' => 'service_client',
                'permissions' => User::getDefaultPermissions(User::ROLE_SERVICE_CLIENT),
            ]
        );
        
        // Create REAL admin user
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'permissions' => User::getDefaultPermissions(User::ROLE_ADMIN),
            ]
        );
    }
}
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the specific admin user requested
        User::firstOrCreate(
            ['email' => 'aaaa@dev.com'],
            [
                'name' => 'aaaa',
                'password' => Hash::make('nouramara'),
                'role' => User::ROLE_ADMIN,
                'permissions' => User::getDefaultPermissions(User::ROLE_ADMIN),
            ]
        );

        // Also ensure the default admin exists
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'permissions' => User::getDefaultPermissions(User::ROLE_ADMIN),
            ]
        );

        echo "✅ Admin users created successfully:\n";
        echo "   - aaaa@dev.com (password: nouramara)\n";
        echo "   - admin@example.com (password: password)\n";
    }
}

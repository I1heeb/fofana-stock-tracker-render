<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default user only if it doesn't exist
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => User::ROLE_PACKAGING_AGENT,
                'permissions' => User::getDefaultPermissions(User::ROLE_PACKAGING_AGENT)
            ]
        );

        // Run other seeders
        $this->call([
            StockSeeder::class,
            UserSeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
            LogSeeder::class,
        ]);
    }
}
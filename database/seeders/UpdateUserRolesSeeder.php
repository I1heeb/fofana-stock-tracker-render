<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UpdateUserRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Set all existing users as packaging_agent by default
        User::whereNull('role')->update(['role' => 'packaging_agent']);
    }
}
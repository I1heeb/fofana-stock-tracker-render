<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Make iheb@admin.com a super admin
        $superAdmin = User::where('email', 'iheb@admin.com')->first();
        
        if ($superAdmin) {
            $superAdmin->update([
                'role' => 'admin',
                'permissions' => [
                    'manage_users',
                    'manage_admins', 
                    'manage_super_admins',
                    'manage_products',
                    'manage_orders',
                    'view_reports',
                    'manage_system',
                    'super_admin' // Special permission for super admin
                ]
            ]);
            
            $this->command->info('✅ iheb@admin.com is now a Super Admin');
        } else {
            // Create super admin if doesn't exist
            User::create([
                'name' => 'Iheb Super Admin',
                'email' => 'iheb@admin.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'permissions' => [
                    'manage_users',
                    'manage_admins', 
                    'manage_super_admins',
                    'manage_products',
                    'manage_orders',
                    'view_reports',
                    'manage_system',
                    'super_admin'
                ]
            ]);
            
            $this->command->info('✅ Created iheb@admin.com as Super Admin');
        }
        
        // Update other admins to have limited permissions
        $otherAdmins = User::where('role', 'admin')
            ->where('email', '!=', 'iheb@admin.com')
            ->get();
            
        foreach ($otherAdmins as $admin) {
            $admin->update([
                'permissions' => [
                    'manage_users', // Can manage regular users
                    'manage_products',
                    'manage_orders',
                    'view_reports'
                    // Cannot manage other admins or super admins
                ]
            ]);
        }
        
        $this->command->info('✅ Updated ' . $otherAdmins->count() . ' other admins with limited permissions');
    }
}

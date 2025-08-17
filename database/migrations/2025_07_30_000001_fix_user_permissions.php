
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        // Update all users to have proper permissions based on their role
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            $permissions = User::getDefaultPermissions($user->role ?? 'packaging_agent');
            
            DB::table('users')
                ->where('id', $user->id)
                ->update(['permissions' => json_encode($permissions)]);
        }
    }

    public function down(): void
    {
        // Reset all permissions to empty array
        DB::table('users')->update(['permissions' => json_encode([])]);
    }
};
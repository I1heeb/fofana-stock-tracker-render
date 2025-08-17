<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        // Mettre à jour les permissions pour tous les utilisateurs selon leurs rôles
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            $permissions = User::getDefaultPermissions($user->role ?? 'service_client');
            
            DB::table('users')
                ->where('id', $user->id)
                ->update(['permissions' => json_encode($permissions)]);
        }
    }

    public function down(): void
    {
        // Réinitialiser toutes les permissions
        DB::table('users')->update(['permissions' => json_encode([])]);
    }
};

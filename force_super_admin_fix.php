<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

try {
    // Direct database update to ensure it works
    $updated = DB::table('users')
        ->where('email', 'nour@gmail.com')
        ->update([
            'is_super_admin' => true,
            'role' => 'admin'
        ]);
    
    if ($updated) {
        echo "✅ FORCED super admin status via direct DB update\n";
        
        // Verify with fresh query
        $user = User::where('email', 'nour@gmail.com')->first();
        echo "📋 Verification:\n";
        echo "   Email: {$user->email}\n";
        echo "   Role: {$user->role}\n";
        echo "   is_super_admin (raw): " . ($user->is_super_admin ? '1' : '0') . "\n";
        echo "   isSuperAdmin() method: " . ($user->isSuperAdmin() ? 'YES' : 'NO') . "\n";
        echo "   canDeleteAdmin(): " . ($user->canDeleteAdmin() ? 'YES' : 'NO') . "\n";
        
        // Also check the database directly
        $dbCheck = DB::table('users')->where('email', 'nour@gmail.com')->first();
        echo "   DB is_super_admin: " . ($dbCheck->is_super_admin ? '1' : '0') . "\n";
        
    } else {
        echo "❌ Update failed - user not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
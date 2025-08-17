<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

try {
    // Force update your user to super admin
    $updated = User::where('email', 'nour@gmail.com')
        ->update([
            'is_super_admin' => 1,
            'role' => 'admin'
        ]);
    
    if ($updated) {
        echo "✅ FORCED super admin status for nour@gmail.com\n";
        
        // Verify
        $user = User::where('email', 'nour@gmail.com')->first();
        echo "📋 Verification:\n";
        echo "   is_super_admin (raw): " . $user->is_super_admin . "\n";
        echo "   isSuperAdmin() method: " . ($user->isSuperAdmin() ? 'true' : 'false') . "\n";
        echo "   canDeleteAdmin(): " . ($user->canDeleteAdmin() ? 'true' : 'false') . "\n";
    } else {
        echo "❌ User not found or update failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
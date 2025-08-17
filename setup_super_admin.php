<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

try {
    // Find or create nour user
    $nour = User::where('email', 'nour@gmail.com')->first();
    
    if ($nour) {
        // Update existing user to super admin
        $nour->update([
            'is_super_admin' => true,
            'role' => 'admin'
        ]);
        echo "✅ Updated existing user: {$nour->name} ({$nour->email}) is now SUPER ADMIN\n";
    } else {
        // Create new super admin user
        $nour = User::create([
            'name' => 'nour',
            'email' => 'nour@gmail.com',
            'password' => bcrypt('nouramara'),
            'role' => 'admin',
            'is_super_admin' => true
        ]);
        echo "✅ Created new SUPER ADMIN: {$nour->name} ({$nour->email})\n";
    }
    
    // Verify super admin status
    if ($nour->isSuperAdmin()) {
        echo "👑 CONFIRMED: {$nour->name} has super admin privileges\n";
        echo "🔑 Login credentials: nour@gmail.com / nouramara\n";
        echo "🎯 This user can now delete other admins\n";
    } else {
        echo "❌ ERROR: Super admin status not set correctly\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

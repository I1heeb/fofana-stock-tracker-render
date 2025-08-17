<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

try {
    echo "📋 CURRENT ADMIN USERS STATUS:\n";
    echo "================================\n\n";
    
    $admins = User::where('role', 'admin')->get();
    
    foreach ($admins as $admin) {
        echo "👤 Name: {$admin->name}\n";
        echo "📧 Email: {$admin->email}\n";
        echo "🔧 Role: {$admin->role}\n";
        echo "👑 Super Admin: " . ($admin->isSuperAdmin() ? 'YES' : 'NO') . "\n";
        echo "🛡️ Can be deleted: " . ($admin->canBeDeleted() ? 'YES' : 'NO') . "\n";
        echo "🗑️ Can delete other admins: " . ($admin->canDeleteAdmin() ? 'YES' : 'NO') . "\n";
        echo "---\n\n";
    }
    
    echo "📊 SUMMARY:\n";
    echo "Total admins: " . $admins->count() . "\n";
    echo "Super admins: " . $admins->where('is_super_admin', true)->count() . "\n";
    echo "Regular admins: " . $admins->where('is_super_admin', false)->count() . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

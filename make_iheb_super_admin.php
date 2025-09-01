<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "🔥 MAKING IHEB SUPER ADMIN\n";
echo "==========================\n\n";

try {
    // Find or create iheb@admin.com
    $iheb = User::where('email', 'iheb@admin.com')->first();
    
    if ($iheb) {
        // Update existing user
        $iheb->update([
            'role' => 'admin',
            'is_super_admin' => true
        ]);
        echo "✅ UPDATED existing user: {$iheb->name} ({$iheb->email})\n";
    } else {
        // Create new super admin user
        $iheb = User::create([
            'name' => 'Iheb Super Admin',
            'email' => 'iheb@admin.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_super_admin' => true
        ]);
        echo "✅ CREATED new user: {$iheb->name} ({$iheb->email})\n";
        echo "🔑 Password: password123\n";
    }
    
    echo "\n🔥 SUPER ADMIN STATUS:\n";
    echo "   Name: {$iheb->name}\n";
    echo "   Email: {$iheb->email}\n";
    echo "   Role: {$iheb->role}\n";
    echo "   Is Super Admin: " . ($iheb->isSuperAdmin() ? 'YES' : 'NO') . "\n";
    echo "   Can Delete Admins: " . ($iheb->canDeleteAdmin() ? 'YES' : 'NO') . "\n";
    echo "   Can Manage Admins: " . ($iheb->canManageAdmins() ? 'YES' : 'NO') . "\n";
    
    echo "\n🎯 SUPER ADMIN PERMISSIONS:\n";
    echo "   ✅ Can edit/delete ALL users (except other super admins)\n";
    echo "   ✅ Can promote admins to super admin\n";
    echo "   ✅ Can demote super admins to regular admin\n";
    echo "   ✅ Can manage all system functions\n";
    echo "   ✅ Full access to admin panel\n";
    
    echo "\n👥 OTHER USERS STATUS:\n";
    $otherUsers = User::where('email', '!=', 'iheb@admin.com')->get();
    foreach ($otherUsers as $user) {
        $status = $user->isSuperAdmin() ? '🔥 SUPER ADMIN' : ($user->isAdmin() ? '👤 ADMIN' : '👷 ' . strtoupper($user->role));
        echo "   {$user->name} ({$user->email}) - {$status}\n";
    }
    
    echo "\n🚀 READY TO USE!\n";
    echo "🌐 Login at: /login\n";
    echo "📧 Email: iheb@admin.com\n";
    echo "🔑 Password: " . ($iheb->wasRecentlyCreated ? 'password123' : 'existing password') . "\n";
    echo "🎯 Go to: /admin/users to manage all users\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📁 File: " . $e->getFile() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
}

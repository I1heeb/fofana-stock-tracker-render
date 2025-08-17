<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

try {
    $user = User::where('email', 'nour@gmail.com')->first();
    
    if ($user) {
        echo "✅ User found:\n";
        echo "   Name: {$user->name}\n";
        echo "   Email: {$user->email}\n";
        echo "   Role: {$user->role}\n";
        echo "   Super Admin: " . ($user->is_super_admin ? 'Yes' : 'No') . "\n";
        echo "   Can delete admins: " . ($user->canDeleteAdmin() ? 'Yes' : 'No') . "\n";
    } else {
        echo "❌ User not found\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

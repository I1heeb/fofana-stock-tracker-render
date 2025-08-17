<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    // Create or update iheb super admin
    $iheb = User::updateOrCreate(
        ['email' => 'iheb@admin.com'],
        [
            'name' => 'Iheb',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'is_super_admin' => true,
            'permissions' => User::getDefaultPermissions('admin'),
        ]
    );
    
    echo "✅ SUPER ADMIN CRÉÉ AVEC SUCCÈS!\n";
    echo "👑 Nom: {$iheb->name}\n";
    echo "📧 Email: {$iheb->email}\n";
    echo "🔑 Mot de passe: 12345678\n";
    echo "🎯 Rôle: {$iheb->role}\n";
    echo "⭐ Super Admin: " . ($iheb->isSuperAdmin() ? 'OUI' : 'NON') . "\n";
    echo "🔥 Peut supprimer des admins: " . ($iheb->canDeleteAdmin() ? 'OUI' : 'NON') . "\n";
    
    echo "\n🚀 PRÊT À UTILISER!\n";
    echo "🌐 Connexion: http://localhost:8000/login\n";
    
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}
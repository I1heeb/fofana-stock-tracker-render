<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== AUTHENTICATION DEBUG SCRIPT ===\n\n";

try {
    // Test database connection
    echo "1. Testing database connection...\n";
    $connection = DB::connection();
    $pdo = $connection->getPdo();
    echo "✓ Database connected successfully!\n\n";
    
    // Check if users table exists
    echo "2. Checking if users table exists...\n";
    $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'users'");
    if (empty($tables)) {
        echo "✗ Users table does not exist!\n";
        echo "Run: php artisan migrate\n\n";
        exit(1);
    }
    echo "✓ Users table exists!\n\n";
    
    // Count users
    echo "3. Counting users in database...\n";
    $userCount = User::count();
    echo "Total users: $userCount\n\n";
    
    // List all users
    echo "4. Listing all users...\n";
    $users = User::select('id', 'name', 'email', 'role', 'created_at')->get();
    
    if ($users->isEmpty()) {
        echo "No users found in database!\n";
        echo "Creating test user...\n";
        
        // Create test user
        $testUser = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);
        
        echo "✓ Test user created with email: test@example.com and password: password\n\n";
    } else {
        echo "Found users:\n";
        foreach ($users as $user) {
            echo "- ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}\n";
        }
        echo "\n";
    }
    
    // Test authentication with actual users in database
    echo "5. Testing authentication with actual users...\n";
    $actualUsers = User::select('email')->get()->pluck('email')->toArray();
    $testCredentials = [];

    // Test with actual users and common passwords
    foreach ($actualUsers as $email) {
        $testCredentials[] = ['email' => $email, 'password' => 'password'];
        $testCredentials[] = ['email' => $email, 'password' => '123456'];
        $testCredentials[] = ['email' => $email, 'password' => 'admin'];
        $testCredentials[] = ['email' => $email, 'password' => 'test'];
    }
    
    foreach ($testCredentials as $credentials) {
        echo "Testing {$credentials['email']} with password '{$credentials['password']}'...\n";
        
        $user = User::where('email', $credentials['email'])->first();
        if (!$user) {
            echo "  ✗ User not found\n";
            continue;
        }
        
        if (Hash::check($credentials['password'], $user->password)) {
            echo "  ✓ Password matches!\n";
        } else {
            echo "  ✗ Password does not match\n";
        }
    }
    
    echo "\n6. Database schema check...\n";
    $columns = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'users' ORDER BY ordinal_position");
    echo "Users table columns:\n";
    foreach ($columns as $column) {
        echo "- {$column->column_name} ({$column->data_type})\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Reset password for the existing user
echo "\n7. Resetting password for existing user...\n";
$fofUser = User::where('email', 'foaf@fev.com')->first();
if ($fofUser) {
    $fofUser->password = Hash::make('password');
    $fofUser->save();
    echo "✓ Password reset for fof@fev.com to 'password'\n";
} else {
    echo "✗ User fof@fev.com not found\n";
}

echo "\n=== DEBUG COMPLETE ===\n";

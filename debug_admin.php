<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Models\Order;

echo "🔍 ADMIN ROUTES DEBUGGING SCRIPT\n";
echo "================================\n\n";

try {
    // Test 1: Check if admin views exist
    echo "📁 CHECKING ADMIN VIEWS:\n";
    $views = [
        'admin.products' => 'resources/views/admin/products.blade.php',
        'admin.orders' => 'resources/views/admin/orders.blade.php', 
        'admin.reports' => 'resources/views/admin/reports.blade.php'
    ];
    
    foreach ($views as $view => $path) {
        if (file_exists($path)) {
            echo "   ✅ {$view} -> {$path} EXISTS\n";
        } else {
            echo "   ❌ {$view} -> {$path} MISSING\n";
        }
    }
    
    echo "\n";
    
    // Test 2: Check database connections
    echo "🗄️ CHECKING DATABASE:\n";
    
    $productCount = Product::count();
    echo "   ✅ Products table accessible: {$productCount} products\n";
    
    $orderCount = Order::count();
    echo "   ✅ Orders table accessible: {$orderCount} orders\n";
    
    $userCount = User::count();
    echo "   ✅ Users table accessible: {$userCount} users\n";
    
    echo "\n";
    
    // Test 3: Check admin user
    echo "👤 CHECKING ADMIN USER:\n";
    $admin = User::where('email', 'nour@gmail.com')->first();
    if ($admin) {
        echo "   ✅ Admin user found: {$admin->name}\n";
        echo "   📧 Email: {$admin->email}\n";
        echo "   🔧 Role: {$admin->role}\n";
        echo "   👑 Is Admin: " . ($admin->isAdmin() ? 'YES' : 'NO') . "\n";
        echo "   🛡️ Is Super Admin: " . ($admin->isSuperAdmin() ? 'YES' : 'NO') . "\n";
    } else {
        echo "   ❌ Admin user not found\n";
    }
    
    echo "\n";
    
    // Test 4: Test AdminController methods directly
    echo "🎛️ TESTING ADMIN CONTROLLER METHODS:\n";
    
    try {
        $products = Product::paginate(20);
        echo "   ✅ Product pagination works: {$products->count()} products loaded\n";
    } catch (Exception $e) {
        echo "   ❌ Product pagination failed: {$e->getMessage()}\n";
    }
    
    try {
        $orders = Order::with('user')->latest()->paginate(20);
        echo "   ✅ Order pagination works: {$orders->count()} orders loaded\n";
    } catch (Exception $e) {
        echo "   ❌ Order pagination failed: {$e->getMessage()}\n";
    }
    
    try {
        $dailySales = Order::whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('total_amount') ?? 0;
        echo "   ✅ Reports query works: Daily sales = \${$dailySales}\n";
    } catch (Exception $e) {
        echo "   ❌ Reports query failed: {$e->getMessage()}\n";
    }
    
    echo "\n";
    
    // Test 5: Check middleware
    echo "🛡️ CHECKING MIDDLEWARE:\n";
    
    $middlewareClasses = [
        'EnsureAdminRole' => 'app/Http/Middleware/EnsureAdminRole.php',
        'RoleMiddleware' => 'app/Http/Middleware/RoleMiddleware.php',
        'CheckRole' => 'app/Http/Middleware/CheckRole.php'
    ];
    
    foreach ($middlewareClasses as $name => $path) {
        if (file_exists($path)) {
            echo "   ✅ {$name} middleware exists\n";
        } else {
            echo "   ❌ {$name} middleware missing\n";
        }
    }
    
    echo "\n";
    
    // Test 6: Check routes
    echo "🛣️ CHECKING ROUTES:\n";
    
    $routes = [
        'admin.products',
        'admin.orders', 
        'admin.reports',
        'admin.dashboard'
    ];
    
    foreach ($routes as $routeName) {
        try {
            $url = route($routeName);
            echo "   ✅ Route '{$routeName}' -> {$url}\n";
        } catch (Exception $e) {
            echo "   ❌ Route '{$routeName}' failed: {$e->getMessage()}\n";
        }
    }
    
    echo "\n✅ DEBUGGING COMPLETE!\n";
    echo "\nIf all tests pass, the issue might be:\n";
    echo "1. Middleware authentication in web context\n";
    echo "2. Session/CSRF token issues\n";
    echo "3. Route caching problems\n";
    echo "\nTry: php artisan route:clear && php artisan config:clear\n";
    
} catch (Exception $e) {
    echo "❌ CRITICAL ERROR: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}\n";
    echo "Line: {$e->getLine()}\n";
    echo "\nStack trace:\n{$e->getTraceAsString()}\n";
}

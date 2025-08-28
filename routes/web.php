<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;


use App\Http\Controllers\ServiceClientController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware('auth')->group(function () {
    // Dashboard route - accessible to all authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Basic authenticated routes (all roles)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Reports routes - accessible to all authenticated users
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
});

// ADMIN ONLY - Admin Panel (user management) - Seul l'admin a accès
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', App\Http\Controllers\Admin\UserManagementController::class);
    Route::post('users/{user}/toggle-status', [App\Http\Controllers\Admin\UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::patch('users/{user}/update-role', [App\Http\Controllers\Admin\UserManagementController::class, 'updateRole'])->name('users.update-role');
});

// Routes principales - Accès contrôlé par les permissions dans les contrôleurs
Route::middleware(['auth'])->group(function () {
    // Users management - Seul l'admin peut gérer les utilisateurs
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Products - Accès selon le rôle
    // Routes spécifiques AVANT la route resource
    Route::get('/products/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');
    Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
    Route::patch('products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjust-stock');
    Route::resource('products', ProductController::class);

    // Orders - Accès selon le rôle
    Route::get('/orders/pending', [OrderController::class, 'pending'])->name('orders.pending');
    Route::get('/orders/export', [OrderController::class, 'export'])->name('orders.export');
    Route::resource('orders', OrderController::class);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');

    // Logs - Accès selon le rôle
    Route::resource('logs', LogController::class);

    // Suppliers - Accès selon le rôle
    Route::resource('suppliers', SupplierController::class);




});



require __DIR__.'/auth.php';

// Fix logout 419 error - redirect to login on session expiry
Route::get('/logout', function () {
    return redirect()->route('login')->with('message', 'You have been logged out.');
})->name('logout.get');

// DEBUG ROUTES - Remove after fixing admin issues
Route::get('/debug/admin-test', function () {
    try {
        return response()->json([
            'status' => 'success',
            'message' => 'Basic routing works',
            'user' => auth()->check() ? [
                'id' => auth()->id(),
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'role' => auth()->user()->role,
                'is_admin' => auth()->user()->isAdmin()
            ] : 'Not authenticated',
            'timestamp' => now()->toDateTimeString()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->middleware('auth');

Route::get('/debug/admin-products-test', function () {
    try {
        $products = \App\Models\Product::paginate(5);
        return response()->json([
            'status' => 'success',
            'message' => 'Product query works',
            'products_count' => $products->count(),
            'total_products' => $products->total(),
            'sample_product' => $products->first() ? [
                'id' => $products->first()->id,
                'name' => $products->first()->name,
                'sku' => $products->first()->sku
            ] : null
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->middleware(['auth', 'admin']);

Route::get('/debug/admin-view-test', function () {
    try {
        $products = \App\Models\Product::paginate(5);
        return view('admin.products', compact('products'));
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->middleware(['auth', 'admin']);

// Route de diagnostic CSRF (temporaire)
Route::get('/csrf-status', function () {
    return response()->json([
        'csrf_token' => csrf_token(),
        'session_id' => session()->getId(),
        'session_driver' => config('session.driver'),
        'session_lifetime' => config('session.lifetime'),
        'app_key_set' => !empty(config('app.key')),
        'sessions_table_exists' => Schema::hasTable('sessions'),
        'active_sessions' => DB::table('sessions')->count(),
        'current_time' => now()->toDateTimeString(),
        'session_data' => [
            'token' => session()->token(),
            'previous_url' => session()->previousUrl(),
            'flash_data' => session()->all()
        ]
    ]);
})->name('csrf.status');

// Route pour rafraîchir le token CSRF
Route::get('/csrf-token', function () {
    return response()->json([
        'csrf_token' => csrf_token()
    ]);
})->name('csrf.token');

// Route spéciale pour le dashboard admin de Nour
Route::get('/admin/nour-dashboard', function () {
    // Vérifier que l'utilisateur est bien Nour Admin
    if (auth()->check() && auth()->user()->email === 'nour@gmail.com') {
        return view('admin.nour-dashboard');
    }

    // Rediriger vers le dashboard admin si ce n'est pas Nour
    return redirect()->route('admin.dashboard');
})->middleware('auth')->name('admin.nour.dashboard');

// Route AJAX pour la recherche de produits dans les commandes
Route::get('/orders/search-products', function (Request $request) {
    $query = Product::with('supplier');

    // Recherche par nom ou SKU
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    $products = $query->orderBy('name', 'asc')->get();

    return response()->json([
        'products' => $products,
        'count' => $products->count()
    ]);
})->middleware('auth')->name('orders.search.products');

// Routes d'urgence pour contourner les problèmes CSRF
Route::get('/emergency-login', function () {
    return view('auth.emergency-login');
})->name('emergency.login');

Route::post('/emergency-login', function (Request $request) {
    // Validation des identifiants
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Tentative de connexion
    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

        // All users go to main dashboard
        return redirect()->intended(route('dashboard'));
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
})->name('emergency.login.submit');

// Debug route
Route::get('/debug-user', function () {
    if (auth()->check()) {
        $user = auth()->user();
        return response()->json([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ?? 'NO ROLE SET',
            'role_display_name' => $user->getRoleDisplayName(),
            'is_admin' => $user->isAdmin(),
            'all_attributes' => $user->getAttributes()
        ]);
    }
    return 'Not authenticated';
})->middleware('auth');

// Test route pour les commandes
Route::get('/test-orders', function () {
    $orders = \App\Models\Order::with(['user', 'orderItems.product'])->latest()->paginate(15);
    $users = \App\Models\User::orderBy('name')->get();
    $statuses = [
        'pending' => 'En attente',
        'processing' => 'En traitement',
        'packed' => 'Emballé',
        'out' => 'Expédié',
        'completed' => 'Terminé',
        'cancelled' => 'Annulé',
        'returned' => 'Retourné'
    ];

    return view('orders.index', compact('orders', 'users', 'statuses'));
})->middleware('auth');

// Route temporaire pour nettoyer les produits de test
Route::get('/clean-test-products', function () {
    $testKeywords = [
        'nour', 'pariatur', 'vel', 'corrupti', 'provident', 'blanditiis',
        'facere', 'voluptas', 'sed', 'placeat', 'soluta', 'fugit',
        'rem', 'et', 'veritatis', 'quasi', 'illum', 'sequi',
        'deleniti', 'ipsum', 'quod', 'in', 'ut', 'molestiae',
        'non', 'nulla', 'autem', 'officiis'
    ];

    $query = \App\Models\Product::query();

    foreach ($testKeywords as $index => $keyword) {
        if ($index === 0) {
            $query->where('name', 'like', "%{$keyword}%");
        } else {
            $query->orWhere('name', 'like', "%{$keyword}%");
        }
    }

    $testProducts = $query->get();

    if ($testProducts->count() === 0) {
        return 'Aucun produit de test trouvé.';
    }

    $deletedProducts = [];
    foreach ($testProducts as $product) {
        $deletedProducts[] = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku
        ];
        $product->delete();
    }

    return response()->json([
        'message' => 'Produits de test supprimés avec succès!',
        'deleted_count' => count($deletedProducts),
        'deleted_products' => $deletedProducts
    ]);
})->middleware('auth');

// Route plus agressive pour supprimer TOUS les produits de test
Route::get('/force-clean-test-products', function () {
    // SKUs spécifiques mentionnés
    $testSkus = [
        'DEP010', 'QZS-731', 'DQB-570', 'BOG-197', 'SFA-152',
        'HGA-963', 'TEG-323', 'WVA-248', 'VLP-651', 'JIL-252',
        'XWW-400', 'GGT-950', 'OZC-505', 'EZG-155', 'DYS-448'
    ];

    $deletedProducts = [];
    $totalDeleted = 0;

    // Supprimer par SKU
    foreach ($testSkus as $sku) {
        $products = \App\Models\Product::where('sku', $sku)->get();
        foreach ($products as $product) {
            $deletedProducts[] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'method' => 'SKU'
            ];
            $product->delete();
            $totalDeleted++;
        }
    }

    // Supprimer par mots-clés dans le nom
    $testKeywords = [
        'nour', 'pariatur', 'vel', 'corrupti', 'provident', 'blanditiis',
        'facere', 'voluptas', 'sed', 'placeat', 'soluta', 'fugit',
        'rem', 'et', 'veritatis', 'quasi', 'illum', 'sequi',
        'deleniti', 'ipsum', 'quod', 'in', 'ut', 'molestiae',
        'non', 'nulla', 'autem', 'officiis', 'lorem', 'dolor',
        'sit', 'amet', 'consectetur', 'adipiscing', 'elit'
    ];

    foreach ($testKeywords as $keyword) {
        $products = \App\Models\Product::where('name', 'like', "%{$keyword}%")->get();
        foreach ($products as $product) {
            // Éviter les doublons
            $alreadyDeleted = collect($deletedProducts)->contains('id', $product->id);
            if (!$alreadyDeleted) {
                $deletedProducts[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'method' => 'KEYWORD'
                ];
                $product->delete();
                $totalDeleted++;
            }
        }
    }

    if ($totalDeleted === 0) {
        return response()->json([
            'message' => 'Aucun produit de test trouvé à supprimer.',
            'deleted_count' => 0
        ]);
    }

    return response()->json([
        'message' => 'SUPPRESSION FORCÉE TERMINÉE!',
        'deleted_count' => $totalDeleted,
        'deleted_products' => $deletedProducts
    ]);
})->middleware('auth');

// Route de diagnostic pour voir tous les produits
Route::get('/debug-products', function () {
    $allProducts = \App\Models\Product::all(['id', 'name', 'sku', 'created_at']);

    return response()->json([
        'total_products' => $allProducts->count(),
        'products' => $allProducts->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'created_at' => $product->created_at->format('Y-m-d H:i:s')
            ];
        })
    ]);
})->middleware('auth');

// Route ULTRA-AGRESSIVE pour supprimer TOUS les produits de test
Route::get('/nuclear-clean-products', function () {
    // Nouveaux SKUs détectés
    $newTestSkus = [
        'RRI-640', 'CXA-4463', 'DOI-096', 'QYU-459', 'KYL-318',
        'OEY-745', 'UNB-596', 'KQD-411', 'YJX-813', 'TZP-261', 'CXA-446'
    ];

    // Anciens SKUs
    $oldTestSkus = [
        'DEP010', 'QZS-731', 'DQB-570', 'BOG-197', 'SFA-152',
        'HGA-963', 'TEG-323', 'WVA-248', 'VLP-651', 'JIL-252',
        'XWW-400', 'GGT-950', 'OZC-505', 'EZG-155', 'DYS-448'
    ];

    $allTestSkus = array_merge($newTestSkus, $oldTestSkus);

    // Nouveaux mots-clés détectés
    $newTestKeywords = [
        'enim', 'rerum', 'ab', 'esse', 'quo', 'veniam', 'alias', 'cumque',
        'id', 'qui', 'cum', 'saepe', 'nisi', 'facilis', 'quis',
        'recusandae', 'reiciendis', 'est', 'consequatur'
    ];

    // Anciens mots-clés
    $oldTestKeywords = [
        'nour', 'pariatur', 'vel', 'corrupti', 'provident', 'blanditiis',
        'facere', 'voluptas', 'sed', 'placeat', 'soluta', 'fugit',
        'rem', 'et', 'veritatis', 'quasi', 'illum', 'sequi',
        'deleniti', 'ipsum', 'quod', 'in', 'ut', 'molestiae',
        'non', 'nulla', 'autem', 'officiis', 'lorem', 'dolor',
        'sit', 'amet', 'consectetur', 'adipiscing', 'elit'
    ];

    $allTestKeywords = array_merge($newTestKeywords, $oldTestKeywords);

    $deletedProducts = [];
    $totalDeleted = 0;

    // 1. Supprimer par SKU exact
    foreach ($allTestSkus as $sku) {
        $products = \App\Models\Product::where('sku', $sku)->get();
        foreach ($products as $product) {
            $deletedProducts[] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'method' => 'SKU_EXACT'
            ];
            $product->delete();
            $totalDeleted++;
        }
    }

    // 2. Supprimer par mots-clés dans le nom
    foreach ($allTestKeywords as $keyword) {
        $products = \App\Models\Product::where('name', 'like', "%{$keyword}%")->get();
        foreach ($products as $product) {
            $alreadyDeleted = collect($deletedProducts)->contains('id', $product->id);
            if (!$alreadyDeleted) {
                $deletedProducts[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'method' => 'KEYWORD_NAME'
                ];
                $product->delete();
                $totalDeleted++;
            }
        }
    }

    // 3. Supprimer les produits avec des noms très courts (probablement du test)
    $shortNameProducts = \App\Models\Product::whereRaw('LENGTH(name) <= 15')
        ->where('name', 'not like', '%Product%')
        ->where('name', 'not like', '%Sony%')
        ->where('name', 'not like', '%MacBook%')
        ->where('name', 'not like', '%Samsung%')
        ->get();

    foreach ($shortNameProducts as $product) {
        $alreadyDeleted = collect($deletedProducts)->contains('id', $product->id);
        if (!$alreadyDeleted) {
            $deletedProducts[] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'method' => 'SHORT_NAME'
            ];
            $product->delete();
            $totalDeleted++;
        }
    }

    return response()->json([
        'message' => '🚀 NETTOYAGE NUCLÉAIRE TERMINÉ!',
        'deleted_count' => $totalDeleted,
        'methods_used' => ['SKU_EXACT', 'KEYWORD_NAME', 'SHORT_NAME'],
        'deleted_products' => $deletedProducts,
        'remaining_products' => \App\Models\Product::count()
    ]);
})->middleware('auth');

// Route pour supprimer TOUS les produits
Route::get('/delete-all-products', function () {
    $allProducts = \App\Models\Product::all();
    $deletedProducts = [];

    foreach ($allProducts as $product) {
        $deletedProducts[] = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'stock' => $product->stock_quantity ?? $product->quantity ?? 0
        ];
        $product->delete();
    }

    return response()->json([
        'message' => '🗑️ TOUS LES PRODUITS SUPPRIMÉS!',
        'deleted_count' => count($deletedProducts),
        'deleted_products' => $deletedProducts,
        'remaining_products' => \App\Models\Product::count()
    ]);
})->middleware('auth');

// Route pour ajouter tous les produits de test supprimés
Route::get('/add-test-products', function () {
    $productsToAdd = [
        ['name' => 'dolorem quo', 'sku' => 'BJB-764', 'stock_quantity' => 25, 'price' => 19.99],
        ['name' => 'ut consectetur', 'sku' => 'KZB-493', 'stock_quantity' => 15, 'price' => 29.99],
        ['name' => 'ut quo', 'sku' => 'SOF-146', 'stock_quantity' => 30, 'price' => 24.99],
        ['name' => 'deleniti rerum', 'sku' => 'VSH-944', 'stock_quantity' => 12, 'price' => 34.99],
        ['name' => 'error eveniet', 'sku' => 'ODC-324', 'stock_quantity' => 8, 'price' => 39.99],
        ['name' => 'illum esse', 'sku' => 'YLQ-736', 'stock_quantity' => 20, 'price' => 22.99],
        ['name' => 'vel numquam', 'sku' => 'VMV-134', 'stock_quantity' => 18, 'price' => 27.99],
        ['name' => 'velit iste', 'sku' => 'VTN-067', 'stock_quantity' => 5, 'price' => 44.99],
        ['name' => 'Penguin bottle', 'sku' => 'PNG-BTL', 'stock_quantity' => 50, 'price' => 12.99],
        ['name' => 'non eum', 'sku' => 'FVE-441', 'stock_quantity' => 22, 'price' => 31.99],
        ['name' => 'enim aut', 'sku' => 'ZPD-151', 'stock_quantity' => 14, 'price' => 26.99],
        ['name' => 'et sed', 'sku' => 'TPO-158', 'stock_quantity' => 35, 'price' => 18.99],
        ['name' => 'inventore dolor', 'sku' => 'QEK-275', 'stock_quantity' => 9, 'price' => 42.99],
        ['name' => 'ut officia', 'sku' => 'ZZQ-565', 'stock_quantity' => 28, 'price' => 23.99],
        ['name' => 'sit inventore', 'sku' => 'EQJ-958', 'stock_quantity' => 16, 'price' => 33.99],
        ['name' => 'non nulla', 'sku' => 'EZG-155', 'stock_quantity' => 48, 'price' => 15.99],
        ['name' => 'ut molestiae', 'sku' => 'OZC-505', 'stock_quantity' => 55, 'price' => 21.99],
        ['name' => 'quod in', 'sku' => 'GGT-950', 'stock_quantity' => 51, 'price' => 25.99],
        ['name' => 'veritatis quasi', 'sku' => 'VLP-651', 'stock_quantity' => 42, 'price' => 28.99],
        ['name' => 'illum sequi', 'sku' => 'JIL-252', 'stock_quantity' => 28, 'price' => 30.99],
        ['name' => 'rem et', 'sku' => 'WVA-248', 'stock_quantity' => 49, 'price' => 17.99],
        ['name' => 'soluta fugit', 'sku' => 'TEG-323', 'stock_quantity' => 11, 'price' => 36.99],
        ['name' => 'sed placeat', 'sku' => 'HGA-963', 'stock_quantity' => 29, 'price' => 24.99],
        ['name' => 'facere voluptas', 'sku' => 'SFA-152', 'stock_quantity' => 26, 'price' => 32.99],
        ['name' => 'deleniti ipsum', 'sku' => 'XWW-400', 'stock_quantity' => 189, 'price' => 14.99],
        ['name' => 'autem officiis', 'sku' => 'DYS-448', 'stock_quantity' => 0, 'price' => 45.99],
        ['name' => 'blanditiis quibusdam', 'sku' => 'FKL-100', 'stock_quantity' => 37, 'price' => 20.99],
        ['name' => 'provident blanditiis', 'sku' => 'BOG-197', 'stock_quantity' => 67, 'price' => 19.99],
        ['name' => 'ut corrupti', 'sku' => 'DQB-570', 'stock_quantity' => 15, 'price' => 38.99],
        ['name' => 'pariatur vel', 'sku' => 'QZS-731', 'stock_quantity' => 7, 'price' => 41.99]
    ];

    $addedProducts = [];

    foreach ($productsToAdd as $productData) {
        // Vérifier si le produit existe déjà
        $existingProduct = \App\Models\Product::where('sku', $productData['sku'])->first();

        if (!$existingProduct) {
            $product = \App\Models\Product::create([
                'name' => $productData['name'],
                'sku' => $productData['sku'],
                'stock_quantity' => $productData['stock_quantity'],
                'quantity' => $productData['stock_quantity'], // Pour compatibilité
                'price' => $productData['price'],
                'low_stock_threshold' => 10,
                'description' => 'Produit de test - ' . $productData['name']
            ]);

            $addedProducts[] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'stock' => $product->stock_quantity,
                'price' => $product->price
            ];
        }
    }

    return response()->json([
        'message' => '✅ PRODUITS DE TEST AJOUTÉS!',
        'added_count' => count($addedProducts),
        'added_products' => $addedProducts,
        'total_products' => \App\Models\Product::count()
    ]);
})->middleware('auth');

// Route pour insertion directe en base de données
Route::get('/insert-products-db', function () {
    $now = now();

    $productsData = [
        ['name' => 'dolorem quo', 'sku' => 'BJB-764', 'stock_quantity' => 25, 'quantity' => 25, 'price' => 19.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - dolorem quo', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'ut consectetur', 'sku' => 'KZB-493', 'stock_quantity' => 15, 'quantity' => 15, 'price' => 29.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - ut consectetur', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'ut quo', 'sku' => 'SOF-146', 'stock_quantity' => 30, 'quantity' => 30, 'price' => 24.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - ut quo', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'deleniti rerum', 'sku' => 'VSH-944', 'stock_quantity' => 12, 'quantity' => 12, 'price' => 34.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - deleniti rerum', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'error eveniet', 'sku' => 'ODC-324', 'stock_quantity' => 8, 'quantity' => 8, 'price' => 39.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - error eveniet', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'illum esse', 'sku' => 'YLQ-736', 'stock_quantity' => 20, 'quantity' => 20, 'price' => 22.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - illum esse', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'vel numquam', 'sku' => 'VMV-134', 'stock_quantity' => 18, 'quantity' => 18, 'price' => 27.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - vel numquam', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'velit iste', 'sku' => 'VTN-067', 'stock_quantity' => 5, 'quantity' => 5, 'price' => 44.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - velit iste', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'Penguin bottle', 'sku' => 'PNG-BTL', 'stock_quantity' => 50, 'quantity' => 50, 'price' => 12.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - Penguin bottle', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'non eum', 'sku' => 'FVE-441', 'stock_quantity' => 22, 'quantity' => 22, 'price' => 31.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - non eum', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'enim aut', 'sku' => 'ZPD-151', 'stock_quantity' => 14, 'quantity' => 14, 'price' => 26.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - enim aut', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'et sed', 'sku' => 'TPO-158', 'stock_quantity' => 35, 'quantity' => 35, 'price' => 18.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - et sed', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'inventore dolor', 'sku' => 'QEK-275', 'stock_quantity' => 9, 'quantity' => 9, 'price' => 42.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - inventore dolor', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'ut officia', 'sku' => 'ZZQ-565', 'stock_quantity' => 28, 'quantity' => 28, 'price' => 23.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - ut officia', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'sit inventore', 'sku' => 'EQJ-958', 'stock_quantity' => 16, 'quantity' => 16, 'price' => 33.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - sit inventore', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'non nulla', 'sku' => 'EZG-155', 'stock_quantity' => 48, 'quantity' => 48, 'price' => 15.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - non nulla', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'ut molestiae', 'sku' => 'OZC-505', 'stock_quantity' => 55, 'quantity' => 55, 'price' => 21.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - ut molestiae', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'quod in', 'sku' => 'GGT-950', 'stock_quantity' => 51, 'quantity' => 51, 'price' => 25.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - quod in', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'veritatis quasi', 'sku' => 'VLP-651', 'stock_quantity' => 42, 'quantity' => 42, 'price' => 28.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - veritatis quasi', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'illum sequi', 'sku' => 'JIL-252', 'stock_quantity' => 28, 'quantity' => 28, 'price' => 30.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - illum sequi', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'rem et', 'sku' => 'WVA-248', 'stock_quantity' => 49, 'quantity' => 49, 'price' => 17.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - rem et', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'soluta fugit', 'sku' => 'TEG-323', 'stock_quantity' => 11, 'quantity' => 11, 'price' => 36.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - soluta fugit', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'sed placeat', 'sku' => 'HGA-963', 'stock_quantity' => 29, 'quantity' => 29, 'price' => 24.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - sed placeat', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'facere voluptas', 'sku' => 'SFA-152', 'stock_quantity' => 26, 'quantity' => 26, 'price' => 32.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - facere voluptas', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'deleniti ipsum', 'sku' => 'XWW-400', 'stock_quantity' => 189, 'quantity' => 189, 'price' => 14.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - deleniti ipsum', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'autem officiis', 'sku' => 'DYS-448', 'stock_quantity' => 0, 'quantity' => 0, 'price' => 45.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - autem officiis', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'blanditiis quibusdam', 'sku' => 'FKL-100', 'stock_quantity' => 37, 'quantity' => 37, 'price' => 20.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - blanditiis quibusdam', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'provident blanditiis', 'sku' => 'BOG-197', 'stock_quantity' => 67, 'quantity' => 67, 'price' => 19.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - provident blanditiis', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'ut corrupti', 'sku' => 'DQB-570', 'stock_quantity' => 15, 'quantity' => 15, 'price' => 38.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - ut corrupti', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'pariatur vel', 'sku' => 'QZS-731', 'stock_quantity' => 7, 'quantity' => 7, 'price' => 41.99, 'low_stock_threshold' => 10, 'description' => 'Produit de test - pariatur vel', 'created_at' => $now, 'updated_at' => $now]
    ];

    try {
        // Supprimer les produits existants avec ces SKUs pour éviter les doublons
        $skus = collect($productsData)->pluck('sku')->toArray();
        \DB::table('products')->whereIn('sku', $skus)->delete();

        // Insertion en lot pour plus d'efficacité
        \DB::table('products')->insert($productsData);

        $totalProducts = \App\Models\Product::count();

        return response()->json([
            'message' => '🚀 INSERTION DIRECTE EN BASE RÉUSSIE!',
            'inserted_count' => count($productsData),
            'total_products_in_db' => $totalProducts,
            'products_inserted' => collect($productsData)->map(function($product) {
                return [
                    'name' => $product['name'],
                    'sku' => $product['sku'],
                    'stock' => $product['stock_quantity'],
                    'price' => '$' . $product['price']
                ];
            })
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur lors de l\'insertion',
            'message' => $e->getMessage()
        ], 500);
    }
})->middleware('auth');

// Route de test pour vérifier la logique de stock faible
Route::get('/test-low-stock', function () {
    $products = \App\Models\Product::all();

    $stockAnalysis = [];

    foreach ($products as $product) {
        $stockAnalysis[] = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'stock_quantity' => $product->stock_quantity,
            'low_stock_threshold' => $product->low_stock_threshold,
            'isLowStock' => $product->isLowStock(),
            'getStockStatusClass' => $product->getStockStatusClass(),
            'status_text' => $product->stock_quantity == 0 ? '❌ Rupture' : ($product->isLowStock() ? '⚠️ Stock Faible' : '✅ Normal'),
        ];
    }

    return response()->json([
        'message' => 'Analyse du stock faible',
        'total_products' => count($stockAnalysis),
        'products' => $stockAnalysis
    ]);
})->middleware('auth');

// Route pour tester spécifiquement les produits en stock faible
Route::get('/debug-low-stock-only', function () {
    $allProducts = \App\Models\Product::all();

    $lowStockProducts = [];
    $normalStockProducts = [];
    $outOfStockProducts = [];

    foreach ($allProducts as $product) {
        $analysis = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'stock_quantity' => $product->stock_quantity,
            'low_stock_threshold' => $product->low_stock_threshold,
            'isLowStock' => $product->isLowStock(),
        ];

        if ($product->stock_quantity == 0) {
            $outOfStockProducts[] = $analysis;
        } elseif ($product->isLowStock()) {
            $lowStockProducts[] = $analysis;
        } else {
            $normalStockProducts[] = $analysis;
        }
    }

    return response()->json([
        'message' => 'Classification des produits par stock',
        'low_stock_products' => $lowStockProducts,
        'low_stock_count' => count($lowStockProducts),
        'normal_stock_products' => $normalStockProducts,
        'normal_stock_count' => count($normalStockProducts),
        'out_of_stock_products' => $outOfStockProducts,
        'out_of_stock_count' => count($outOfStockProducts),
        'total_products' => $allProducts->count()
    ]);
})->middleware('auth');

// Route pour forcer la mise à jour des seuils de stock faible
Route::get('/fix-low-stock-thresholds', function () {
    $products = \App\Models\Product::all();
    $updated = 0;

    foreach ($products as $product) {
        if (is_null($product->low_stock_threshold) || $product->low_stock_threshold == 0) {
            $product->update(['low_stock_threshold' => 10]);
            $updated++;
        }
    }

    return response()->json([
        'message' => 'Seuils de stock faible mis à jour',
        'updated_products' => $updated,
        'total_products' => $products->count()
    ]);
})->middleware('auth');

// Route de test pour vérifier la page Low Stock
Route::get('/test-low-stock-page', function () {
    $allProducts = \App\Models\Product::all();

    // Même logique que dans le contrôleur
    $lowStockProducts = $allProducts->filter(function ($product) {
        $stock = (int) ($product->stock_quantity ?? 0);
        $threshold = (int) ($product->low_stock_threshold ?? 10);

        // Inclure les produits en stock faible ET en rupture (stock <= seuil)
        return $stock <= $threshold;
    })->sortBy('stock_quantity');

    $shouldShowInLowStock = [];
    $shouldNotShow = [];

    foreach ($allProducts as $product) {
        $stock = (int) ($product->stock_quantity ?? 0);
        $threshold = (int) ($product->low_stock_threshold ?? 10);

        $productInfo = [
            'name' => $product->name,
            'sku' => $product->sku,
            'stock' => $stock,
            'threshold' => $threshold,
            'should_show' => $stock <= $threshold
        ];

        if ($stock <= $threshold) {
            $shouldShowInLowStock[] = $productInfo;
        } else {
            $shouldNotShow[] = $productInfo;
        }
    }

    return response()->json([
        'message' => 'Test de la logique Low Stock Page',
        'products_that_SHOULD_show' => $shouldShowInLowStock,
        'products_that_should_NOT_show' => $shouldNotShow,
        'total_low_stock' => count($shouldShowInLowStock),
        'total_normal_stock' => count($shouldNotShow)
    ]);
})->middleware('auth');

// Route de test SQL simple
Route::get('/test-sql-low-stock', function () {
    // Test de la requête SQL exacte utilisée dans le contrôleur
    $lowStockProducts = \App\Models\Product::where(function($q) {
        $q->whereRaw('stock_quantity <= COALESCE(low_stock_threshold, 10)');
    })->orderBy('stock_quantity', 'asc')->get();

    $allProducts = \App\Models\Product::all();

    return response()->json([
        'message' => 'Test de la requête SQL Low Stock',
        'sql_query' => 'stock_quantity <= COALESCE(low_stock_threshold, 10)',
        'low_stock_products_found' => $lowStockProducts->map(function($p) {
            return [
                'name' => $p->name,
                'sku' => $p->sku,
                'stock_quantity' => $p->stock_quantity,
                'low_stock_threshold' => $p->low_stock_threshold,
                'should_show' => true
            ];
        }),
        'total_low_stock_found' => $lowStockProducts->count(),
        'total_products_in_db' => $allProducts->count(),
        'sample_all_products' => $allProducts->take(5)->map(function($p) {
            return [
                'name' => $p->name,
                'stock_quantity' => $p->stock_quantity,
                'low_stock_threshold' => $p->low_stock_threshold,
                'meets_criteria' => $p->stock_quantity <= ($p->low_stock_threshold ?? 10)
            ];
        })
    ]);
})->middleware('auth');

// Test route for Submit Button functionality (development only)
Route::get('/test-submit-button', function () {
    return view('test-submit-button');
})->name('test.submit.button');

// Simple Create Order route for testing
Route::get('/orders/create-simple', function () {
    $products = App\Models\Product::all();
    return view('orders.create-simple', compact('products'));
})->middleware('auth')->name('orders.create.simple');

// Test Product Edit route
Route::get('/test-product-edit', function () {
    $product = App\Models\Product::first();
    if (!$product) {
        return redirect('/products')->with('error', 'No products found. Please create a product first.');
    }
    return redirect()->route('products.edit', $product);
})->middleware('auth')->name('test.product.edit');

// Test Product View route
Route::get('/test-product-view', function () {
    $product = App\Models\Product::first();
    if (!$product) {
        return redirect('/products')->with('error', 'No products found. Please create a product first.');
    }
    return redirect()->route('products.show', $product);
})->middleware('auth')->name('test.product.view');

// Admin Routes - Use consistent middleware
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('index');
    Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/debug', [App\Http\Controllers\AdminController::class, 'debug'])->name('debug');
    Route::get('/products', [App\Http\Controllers\AdminController::class, 'products'])->name('products');
    Route::get('/orders', [App\Http\Controllers\AdminController::class, 'orders'])->name('orders');
    Route::get('/reports', [App\Http\Controllers\AdminController::class, 'reports'])->name('reports');

    // User management (legacy routes - keeping for compatibility)
    Route::get('/users/create', [App\Http\Controllers\AdminController::class, 'createUser'])->name('users.create-legacy');
    Route::post('/users', [App\Http\Controllers\AdminController::class, 'storeUser'])->name('users.store-legacy');
    Route::patch('/users/{user}/role', [App\Http\Controllers\AdminController::class, 'updateUserRole'])->name('users.update-role-legacy');
    Route::delete('/users/{user}', [App\Http\Controllers\AdminController::class, 'deleteUser'])->name('users.delete-legacy');

    // Super Admin management
    Route::post('/users/{user}/make-super-admin', [App\Http\Controllers\AdminController::class, 'makeSuperAdmin'])->name('users.make-super-admin');
    Route::post('/users/{user}/remove-super-admin', [App\Http\Controllers\AdminController::class, 'removeSuperAdmin'])->name('users.remove-super-admin');
});

// Debug page for admin issues
Route::get('/debug/admin-dashboard', function () {
    return view('debug.admin');
})->middleware('auth')->name('debug.admin');

// Debug UserManagementController specifically
Route::get('/debug/user-management-test', function () {
    try {
        \Log::info('UserManagementController debug test started');

        // Test the exact same logic as UserManagementController::index()
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not admin',
                'user_role' => auth()->user()->role,
                'is_admin' => auth()->user()->isAdmin()
            ], 403);
        }

        $users = \App\Models\User::latest()->paginate(15);

        \Log::info('Users loaded successfully', ['count' => $users->count()]);

        return response()->json([
            'status' => 'success',
            'message' => 'UserManagementController logic works',
            'users_count' => $users->count(),
            'total_users' => $users->total(),
            'sample_user' => $users->first() ? [
                'id' => $users->first()->id,
                'name' => $users->first()->name,
                'email' => $users->first()->email,
                'role' => $users->first()->role
            ] : null
        ]);

    } catch (\Exception $e) {
        \Log::error('UserManagementController debug test failed', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'controller' => 'UserManagementController debug'
        ], 500);
    }
})->middleware('auth');

// Test the actual view rendering
Route::get('/debug/user-management-view-test', function () {
    try {
        \Log::info('UserManagementController view test started');

        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $users = \App\Models\User::latest()->paginate(15);

        \Log::info('About to render admin.users.index view');
        return view('admin.users.index', compact('users'));

    } catch (\Exception $e) {
        \Log::error('UserManagementController view test failed', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'view' => 'admin.users.index'
        ], 500);
    }
})->middleware(['auth', 'role:admin']);

// Simple admin products test without middleware
Route::get('/debug/simple-admin-products', function () {
    try {
        \Log::info('Simple admin products test started');

        $products = \App\Models\Product::take(5)->get();

        \Log::info('Products loaded successfully', ['count' => $products->count()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Simple products query works',
            'products_count' => $products->count(),
            'products' => $products->map(function($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'stock' => $p->stock_quantity
                ];
            })
        ]);

    } catch (\Exception $e) {
        \Log::error('Simple admin products test failed', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString())
        ], 500);
    }
});

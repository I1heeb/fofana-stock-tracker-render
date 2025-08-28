@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">🔍 Admin Debug Dashboard</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Authentication Status -->
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="font-semibold text-blue-900 mb-2">👤 Authentication Status</h3>
                @auth
                    <p class="text-green-600">✅ Authenticated</p>
                    <p><strong>Name:</strong> {{ auth()->user()->name }}</p>
                    <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
                    <p><strong>Role:</strong> {{ auth()->user()->role }}</p>
                    <p><strong>Is Admin:</strong> {{ auth()->user()->isAdmin() ? 'YES' : 'NO' }}</p>
                    <p><strong>Is Super Admin:</strong> {{ auth()->user()->isSuperAdmin() ? 'YES' : 'NO' }}</p>
                @else
                    <p class="text-red-600">❌ Not Authenticated</p>
                @endauth
            </div>

            <!-- Database Status -->
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="font-semibold text-green-900 mb-2">🗄️ Database Status</h3>
                @php
                    try {
                        $productCount = \App\Models\Product::count();
                        $orderCount = \App\Models\Order::count();
                        $userCount = \App\Models\User::count();
                        $dbStatus = 'Connected';
                    } catch (\Exception $e) {
                        $dbStatus = 'Error: ' . $e->getMessage();
                        $productCount = $orderCount = $userCount = 'N/A';
                    }
                @endphp
                
                <p class="text-green-600">✅ {{ $dbStatus }}</p>
                <p><strong>Products:</strong> {{ $productCount }}</p>
                <p><strong>Orders:</strong> {{ $orderCount }}</p>
                <p><strong>Users:</strong> {{ $userCount }}</p>
            </div>

            <!-- View Status -->
            <div class="bg-yellow-50 p-4 rounded-lg">
                <h3 class="font-semibold text-yellow-900 mb-2">📄 View Status</h3>
                @php
                    $views = [
                        'admin.products' => 'resources/views/admin/products.blade.php',
                        'admin.orders' => 'resources/views/admin/orders.blade.php',
                        'admin.reports' => 'resources/views/admin/reports.blade.php'
                    ];
                @endphp
                
                @foreach($views as $view => $path)
                    @if(view()->exists($view))
                        <p class="text-green-600">✅ {{ $view }}</p>
                    @else
                        <p class="text-red-600">❌ {{ $view }}</p>
                    @endif
                @endforeach
            </div>

            <!-- Route Status -->
            <div class="bg-purple-50 p-4 rounded-lg">
                <h3 class="font-semibold text-purple-900 mb-2">🛣️ Route Status</h3>
                @php
                    $routes = [
                        'admin.dashboard',
                        'admin.products', 
                        'admin.orders',
                        'admin.reports'
                    ];
                @endphp
                
                @foreach($routes as $routeName)
                    @try
                        @php $url = route($routeName); @endphp
                        <p class="text-green-600">✅ {{ $routeName }} → {{ $url }}</p>
                    @catch(\Exception $e)
                        <p class="text-red-600">❌ {{ $routeName }} → {{ $e->getMessage() }}</p>
                    @endtry
                @endforeach
            </div>
        </div>

        <!-- Test Links -->
        <div class="mt-8">
            <h3 class="font-semibold text-gray-900 mb-4">🧪 Test Links</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="/debug/admin-test" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 text-center">
                    Basic Test
                </a>
                <a href="/debug/admin-products-test" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 text-center">
                    Products Test
                </a>
                <a href="/debug/admin-view-test" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 text-center">
                    View Test
                </a>
                <a href="{{ route('admin.debug') }}" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 text-center">
                    Controller Test
                </a>
            </div>
        </div>

        <!-- Actual Admin Links -->
        <div class="mt-8">
            <h3 class="font-semibold text-gray-900 mb-4">🎛️ Admin Pages (These are causing 500 errors)</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('admin.products') }}" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 text-center">
                    Admin Products (500 Error)
                </a>
                <a href="{{ route('admin.orders') }}" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 text-center">
                    Admin Orders (500 Error)
                </a>
                <a href="{{ route('admin.reports') }}" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 text-center">
                    Admin Reports (500 Error)
                </a>
            </div>
        </div>

        <!-- Instructions -->
        <div class="mt-8 bg-gray-50 p-4 rounded-lg">
            <h3 class="font-semibold text-gray-900 mb-2">📋 Debugging Instructions</h3>
            <ol class="list-decimal list-inside space-y-2 text-sm">
                <li>Click each "Test" button above and check if they work</li>
                <li>If test buttons work but admin pages don't, the issue is in the AdminController methods</li>
                <li>Check browser console for JavaScript errors</li>
                <li>Check network tab for the exact error response</li>
                <li>Look at the JSON error response for file and line number</li>
                <li>Share the exact error details for targeted fixes</li>
            </ol>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">🛡️ Admin Dashboard</h1>
            <p class="text-gray-600 mt-2">Welcome back, {{ Auth::user()->name }}!</p>
        </div>
        <div class="text-sm text-gray-500">
            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full font-medium">
                🔑 Admin Access
            </span>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Users -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
                </div>
                <div class="text-4xl text-blue-500">👥</div>
            </div>
        </div>

        <!-- Total Products -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Products</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_products'] }}</p>
                </div>
                <div class="text-4xl text-green-500">📦</div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Orders</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_orders'] }}</p>
                </div>
                <div class="text-4xl text-yellow-500">📋</div>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pending Orders</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['pending_orders'] }}</p>
                </div>
                <div class="text-4xl text-red-500">⏳</div>
            </div>
        </div>
    </div>

    <!-- Revenue & Low Stock -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Revenue Card -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">💰 Total Revenue</h3>
                <span class="text-2xl">💵</span>
            </div>
            <p class="text-3xl font-bold text-green-600">${{ number_format($stats['total_revenue'], 2) }}</p>
            <p class="text-sm text-gray-600 mt-2">From completed orders</p>
        </div>

        <!-- Low Stock Alert -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">⚠️ Low Stock Alert</h3>
                <span class="text-2xl">📉</span>
            </div>
            <p class="text-3xl font-bold text-red-600">{{ $stats['low_stock_products'] }}</p>
            <p class="text-sm text-gray-600 mt-2">Products need restocking</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">🚀 Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.users') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <span class="text-2xl mr-3">👥</span>
                <div>
                    <p class="font-medium text-blue-900">Manage Users</p>
                    <p class="text-sm text-blue-600">View and edit users</p>
                </div>
            </a>

            <a href="{{ route('admin.products') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                <span class="text-2xl mr-3">📦</span>
                <div>
                    <p class="font-medium text-green-900">Manage Products</p>
                    <p class="text-sm text-green-600">View and edit products</p>
                </div>
            </a>

            <a href="{{ route('admin.orders') }}" class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                <span class="text-2xl mr-3">📋</span>
                <div>
                    <p class="font-medium text-yellow-900">Manage Orders</p>
                    <p class="text-sm text-yellow-600">View and process orders</p>
                </div>
            </a>

            <a href="{{ route('admin.reports') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                <span class="text-2xl mr-3">📊</span>
                <div>
                    <p class="font-medium text-purple-900">View Reports</p>
                    <p class="text-sm text-purple-600">Sales and analytics</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Recent Orders</h3>
            <div class="space-y-3">
                @forelse($stats['recent_orders'] as $order)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">Order #{{ $order->id }}</p>
                            <p class="text-sm text-gray-600">{{ $order->user->name ?? 'Unknown User' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-green-600">${{ number_format($order->total_amount, 2) }}</p>
                            <p class="text-sm text-gray-500">{{ $order->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No recent orders</p>
                @endforelse
            </div>
        </div>

        <!-- Low Stock Products -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">⚠️ Low Stock Products</h3>
            <div class="space-y-3">
                @forelse($stats['low_stock_products_list'] as $product)
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">{{ $product->name }}</p>
                            <p class="text-sm text-gray-600">SKU: {{ $product->sku }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-red-600">{{ $product->stock_quantity }} left</p>
                            <p class="text-sm text-gray-500">Threshold: {{ $product->low_stock_threshold }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">All products are well stocked</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

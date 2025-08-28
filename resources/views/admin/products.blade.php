@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">📦 Product Management</h1>
            <p class="text-gray-600 mt-2">Manage inventory and product information</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
            ← Back to Admin Dashboard
        </a>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">All Products ({{ $products->total() }})</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                    <div class="text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($product->description ?? '', 50) }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-mono text-gray-900">{{ $product->sku }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-green-600">${{ number_format($product->price, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <span class="font-medium">{{ $product->stock_quantity }}</span> units
                                    <div class="text-xs text-gray-500">
                                        Min: {{ $product->minimum_stock }} | Threshold: {{ $product->low_stock_threshold }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->stock_quantity <= $product->low_stock_threshold)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        ⚠️ Low Stock
                                    </span>
                                @elseif($product->stock_quantity <= $product->minimum_stock)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        ⚡ Critical
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        ✅ In Stock
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $product->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No products found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- Stock Status Summary -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="text-3xl text-green-500">✅</div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-green-900">In Stock</h3>
                    <p class="text-2xl font-bold text-green-600">
                        {{ $products->where('stock_quantity', '>', function($product) { return $product->low_stock_threshold; })->count() }}
                    </p>
                    <p class="text-sm text-green-600">Products well stocked</p>
                </div>
            </div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="text-3xl text-yellow-500">⚠️</div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-yellow-900">Low Stock</h3>
                    <p class="text-2xl font-bold text-yellow-600">
                        {{ $products->filter(function($product) { return $product->stock_quantity <= $product->low_stock_threshold && $product->stock_quantity > $product->minimum_stock; })->count() }}
                    </p>
                    <p class="text-sm text-yellow-600">Need restocking soon</p>
                </div>
            </div>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="text-3xl text-red-500">🚨</div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-red-900">Critical</h3>
                    <p class="text-2xl font-bold text-red-600">
                        {{ $products->filter(function($product) { return $product->stock_quantity <= $product->minimum_stock; })->count() }}
                    </p>
                    <p class="text-sm text-red-600">Immediate attention needed</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

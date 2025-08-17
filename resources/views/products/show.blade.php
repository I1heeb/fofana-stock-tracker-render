@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-navy-900">📦 Product Details</h2>
            <p class="text-gray-600 mt-1">View and manage product information</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('products.index') }}" class="btn-secondary">
                ← Back to Products
            </a>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('products.edit', $product) }}" class="btn-primary">
                    ✏️ Edit Product
                </a>
            @endif
        </div>
    </div>

    <!-- Product Information Card -->
    <div class="modern-card">
        <div class="p-6">
            <!-- Product Header -->
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
                    <p class="text-gray-600 mt-1">{{ $product->description ?? 'No description available' }}</p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-green-600">${{ number_format($product->price, 2) }}</div>
                    <p class="text-sm text-gray-500">Price per unit</p>
                </div>
            </div>

            <!-- Product Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- SKU -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="text-2xl mr-3">🏷️</div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">SKU</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $product->sku ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Barcode -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="text-2xl mr-3">📊</div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Barcode</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $product->barcode ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Stock Quantity -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="text-2xl mr-3">📦</div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Current Stock</p>
                            <p class="text-lg font-semibold {{ $product->stock_quantity <= $product->low_stock_threshold ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $product->stock_quantity }} units
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Stock Status -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="text-2xl mr-3">
                            @if($product->stock_quantity <= 0)
                                🚫
                            @elseif($product->stock_quantity <= $product->minimum_stock)
                                🚨
                            @elseif($product->stock_quantity <= $product->low_stock_threshold)
                                ⚠️
                            @else
                                ✅
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Status</p>
                            <p class="text-lg font-semibold
                                @if($product->stock_quantity <= 0) text-red-600
                                @elseif($product->stock_quantity <= $product->minimum_stock) text-red-600
                                @elseif($product->stock_quantity <= $product->low_stock_threshold) text-yellow-600
                                @else text-green-600 @endif">
                                @if($product->stock_quantity <= 0)
                                    Out of Stock
                                @elseif($product->stock_quantity <= $product->minimum_stock)
                                    Critical
                                @elseif($product->stock_quantity <= $product->low_stock_threshold)
                                    Low Stock
                                @else
                                    In Stock
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Thresholds -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h3 class="font-semibold text-yellow-900 mb-2">📊 Stock Thresholds</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Minimum Stock:</span>
                            <span class="font-medium">{{ $product->minimum_stock }} units</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Low Stock Threshold:</span>
                            <span class="font-medium">{{ $product->low_stock_threshold }} units</span>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-900 mb-2">📅 Product Info</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Created:</span>
                            <span class="font-medium">{{ $product->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Last Updated:</span>
                            <span class="font-medium">{{ $product->updated_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Management (Admin Only) -->
    @if(auth()->user()->isAdmin())
    <div class="modern-card">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🔧 Stock Management (Admin Only)</h3>

            <form action="{{ route('products.adjust-stock', $product) }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Stock Adjustment</label>
                        <input type="number" name="adjustment"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="+50 or -10" required>
                        <p class="text-xs text-gray-500 mt-1">Positive to add, negative to remove</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                        <input type="text" name="reason"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="e.g., Restock, Inventory correction..." required>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">
                        ⚡ Adjust Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
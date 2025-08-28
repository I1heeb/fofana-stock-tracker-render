@extends('layouts.app')

@section('content')
<div class="space-y-6">
@if(request('filter') !== 'low_stock')
 <div class="flex justify-between items-center">
    <div>
        <h2 class="text-3xl font-bold text-navy-900">Products</h2>
        <p class="text-gray-600 mt-1">{{ $products->total() ?? $products->count() }} products in inventory</p>
    </div>
    <div class="flex space-x-3">
        <a href="{{ route('products.index', ['filter' => 'low_stock']) }}" class="btn-secondary">
            Low Stock Items
        </a>
        @can('create', App\Models\Product::class)
            <a href="{{ route('products.create') }}" class="btn-primary">
                + Add Product
            </a>
        @endcan 
    </div>
</div>
@endif



<!-- Advanced Product Search -->
<div class="modern-card p-6 mb-6">
    <!-- Collapse Header -->
    <div class="mb-4">
        <button class="flex items-center w-full text-left p-0 bg-transparent border-none focus:outline-none"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#productSearchFilters"
                aria-expanded="true"
                aria-controls="productSearchFilters">
            <svg class="h-5 w-5 text-blue-600 mr-2 transition-transform" id="productFilterIcon" style="transform: rotate(180deg);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
            <h3 class="text-lg font-semibold text-navy-900">🔍 Product Search & Filters</h3>
        </button>
    </div>
    
    <!-- Collapsible Content -->
    <div class="collapse show" id="productSearchFilters" style="min-height: 50px; border: 1px solid #e5e7eb; background: #f9fafb; display: block !important; visibility: visible !important; opacity: 1 !important;">
        <form method="GET" action="{{ route('products.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Name/SKU Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Product Name or SKU</label>
                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ request('search') }}"
                        placeholder="Search by name or SKU..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- Stock Status Filter -->
                <div>
                    <label for="stock_status" class="block text-sm font-medium text-gray-700 mb-1">Stock Status</label>
                    <select name="stock_status" id="stock_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Products</option>
                        <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>✅ In Stock</option>
                        <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>⚠️ Low Stock</option>
                        <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>❌ Out of Stock</option>
                    </select>
                </div>

                <!-- Price Min -->
                <div>
                    <label for="price_min" class="block text-sm font-medium text-gray-700 mb-1">Min Price</label>
                    <input
                        type="number"
                        name="price_min"
                        id="price_min"
                        value="{{ request('price_min') }}"
                        placeholder="0.00"
                        step="0.01"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- Price Max -->
                <div>
                    <label for="price_max" class="block text-sm font-medium text-gray-700 mb-1">Max Price</label>
                    <input
                        type="number"
                        name="price_max"
                        id="price_max"
                        value="{{ request('price_max') }}"
                        placeholder="999.99"
                        step="0.01"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Created From</label>
                    <input
                        type="date"
                        name="date_from"
                        id="date_from"
                        value="{{ request('date_from') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Created To</label>
                    <input
                        type="date"
                        name="date_to"
                        id="date_to"
                        value="{{ request('date_to') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
            </div>

            <!-- Action Buttons & Results -->
            <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                <div class="flex space-x-2">
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                    >
                        🔍 Search
                    </button>
                    <a
                        href="{{ route('products.index') }}"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors"
                    >
                        🔄 Clear All Filters
                    </a>
                </div>

                @if(request()->hasAny(['search', 'stock_status', 'date_from', 'date_to', 'price_min', 'price_max']))
                    <div class="text-sm text-gray-600">
                        <strong>{{ $products->total() ?? $products->count() }} product(s) found</strong>
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>

<script>
// Ensure DOM is loaded before adding event listeners
document.addEventListener('DOMContentLoaded', function() {
    const filterElement = document.getElementById('productSearchFilters');
    const iconElement = document.getElementById('productFilterIcon');

    if (filterElement && iconElement) {
        // Rotate arrow icon on collapse toggle
        filterElement.addEventListener('show.bs.collapse', function () {
            iconElement.style.transform = 'rotate(180deg)';
        });

        filterElement.addEventListener('hide.bs.collapse', function () {
            iconElement.style.transform = 'rotate(0deg)';
        });
    }
});
</script>

    @if(isset($error))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <strong>Error:</strong> {{ $error }}
        </div>
    @endif

    @if($products->count() > 0)
        <!-- Products Table -->
        <div class="modern-card overflow-hidden">
            <table class="modern-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($products as $product)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $product->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $product->sku }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->getStockStatusClass() }}">
                                        {{ $product->stock_quantity ?? 0 }}
                                    </span>
                                    @if($product->stock_quantity == 0)
                                        <span class="text-red-600 text-xs font-medium">❌ Rupture</span>
                                    @elseif($product->isLowStock())
                                        <span class="text-yellow-600 text-xs font-medium">⚠️ Stock Faible</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    Seuil: {{ $product->low_stock_threshold ?? 10 }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ number_format($product->price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-3">
                                    <a href="{{ route('products.show', $product->id) }}" class="text-navy-600 hover:text-navy-900 font-medium">👁️ View</a>
                                    <a href="{{ route('products.edit', $product->id) }}" class="text-mustard-600 hover:text-mustard-700 font-medium">✏️ Edit</a>
                                    @can('delete', $product)
                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-medium" onclick="return confirm('Are you sure?')">🗑️ Delete</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            @if($products->hasPages())
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Affichage de {{ $products->firstItem() }} à {{ $products->lastItem() }} sur {{ $products->total() }} produits
                    </div>
                    <div>
                        {{ $products->links() }}
                    </div>
                </div>
            @endif
        </div>
    @else
        <!-- Empty state - No products found -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun produit trouvé</h3>
            <p class="mt-1 text-sm text-gray-500">Commencez par créer votre premier produit.</p>
            <div class="mt-6">
                @can('create', App\Models\Product::class)
                    <a href="{{ route('products.create') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Ajouter votre premier produit
                    </a>
                @endcan
            </div>
        </div>
    @endif
</div>

@endsection

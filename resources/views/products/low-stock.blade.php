@extends('layouts.app')

@section('content')
<div class="space-y-6">
 <div class="flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold">
            @if(request('filter') === 'low_stock')
                ⚠️ Low Stock Products ({{ $products->total() ?? $products->count() }})
            @else
                Products ({{ $products->total() ?? $products->count() }})
            @endif
        </h2>
        @if(request('filter') === 'low_stock')
            <p class="text-sm text-red-600 mt-1">Showing only products with stock ≤ threshold</p>
        @endif
    </div>
    <div class="flex gap-2">
        @if(request('filter') === 'low_stock')
            <a href="{{ route('products.index') }}"
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                Show All Products
            </a>
        @endif
        @can('create', App\Models\Product::class)
            <a href="{{ route('products.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                + Add Product
            </a>
        @endcan
    </div>
</div>

<!-- Low Stock Alert -->
@if(isset($lowStockProducts) && $lowStockProducts->count() > 0 && request('filter') !== 'low_stock')
    <div class="bg-red-50 border-l-4 border-red-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">⚠️ Low Stock Alert</h3>
                <div class="mt-2 text-sm text-red-700">
                    <p class="mb-2">The following products are running low on stock:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($lowStockProducts->take(8) as $product)
                            <li>
                                <strong>{{ $product->name }}</strong>
                                ({{ $product->stock_quantity }} remaining, threshold: {{ $product->low_stock_threshold ?? 10 }})
                                @if($product->stock_quantity == 0)
                                    <span class="text-red-600 font-bold">- OUT OF STOCK</span>
                                @endif
                            </li>
                        @endforeach
                        @if($lowStockProducts->count() > 8)
                            <li class="text-red-600 font-medium">
                                ... and {{ $lowStockProducts->count() - 8 }} more items need attention
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Barre de recherche -->
<div class="mb-6">
    <form method="GET" action="{{ route('products.index') }}" class="flex gap-4">
        <div class="flex-1">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}"
                placeholder="Rechercher par nom ou SKU..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        </div>
        <button 
            type="submit" 
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
            Rechercher
        </button>
        @if(request('search'))
            <a
                href="{{ route('products.index') }}"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
            >
                Effacer
            </a>
        @endif
        @if(request('filter') !== 'low_stock')
            <a href="{{ route('products.index', ['filter' => 'low_stock']) }}"
               class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors">
                ⚠️ Low Stock Only
            </a>
        @endif
    </form>
</div>

    @if(isset($error))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <strong>Error:</strong> {{ $error }}
        </div>
    @endif

    @if($products->count() > 0)
        <!-- Simple Table View -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <table class="min-w-full divide-y divide-gray-200">
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
                                <a href="{{ route('products.edit', $product->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                @can('delete', $product)
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                @endcan
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
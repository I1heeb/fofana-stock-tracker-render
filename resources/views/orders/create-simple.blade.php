@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">📋 Create Order</h1>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">There were errors with your submission:</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
        @csrf
        
        <!-- Search Bar -->
        <div class="mb-6">
            <div class="flex gap-3 mb-4">
                <div class="flex-1">
                    <input
                        type="text"
                        id="productSearch"
                        placeholder="🔍 Search products by name, SKU, or any text..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
                <button
                    type="button"
                    id="searchBtn"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    🔍 Search
                </button>
                <button
                    type="button"
                    id="submitBtn"
                    class="px-4 py-3 bg-yellow-500 text-blue-900 rounded-lg hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400"
                    title="Submit the order (same as pressing Enter)"
                >
                    📋 Submit Order
                </button>
            </div>

            <!-- Product count info -->
            <div class="text-sm text-gray-600 mb-2" id="productCount">
                📋 {{ $products->count() }} product(s) available
                <span class="ml-4 text-gray-500">💡 Type to search • Click Submit Order or use Ctrl+Enter to submit</span>
            </div>
        </div>
        
        <!-- Products Selection -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <label class="block text-sm font-medium text-gray-700">📦 Select Products</label>
            </div>
            
            <div class="space-y-4" id="productsContainer">
                @forelse($products as $product)
                    <div class="border rounded-lg p-4 hover:bg-gray-50 {{ $product->stock_quantity == 0 ? 'bg-red-50 border-red-200' : '' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-medium text-gray-900">{{ $product->name }}</h3>
                                    @if($product->created_at->diffInDays(now()) <= 7)
                                        <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full font-medium">
                                            🆕 Nouveau
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 mt-1">
                                    📦 SKU: {{ $product->sku }} | 
                                    📊 {{ $product->stock_quantity }} in stock
                                    @if($product->stock_quantity == 0)
                                        <span class="text-red-500 font-bold">(RUPTURE DE STOCK)</span>
                                    @endif
                                </p>
                                <div class="flex items-center gap-4">
                                    <p class="text-sm font-medium text-green-600">💰 ${{ number_format($product->price, 2) }} / unité</p>
                                    <p class="text-xs text-gray-400">📅 Ajouté le {{ $product->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <label class="text-sm font-medium text-gray-700">Quantity:</label>
                                <input 
                                    type="number" 
                                    name="products[{{ $product->id }}][quantity]" 
                                    min="0" 
                                    max="{{ $product->stock_quantity }}" 
                                    value="0"
                                    data-price="{{ $product->price }}"
                                    data-product-name="{{ $product->name }}"
                                    class="w-20 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $product->stock_quantity == 0 ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                    {{ $product->stock_quantity == 0 ? 'disabled' : '' }}
                                >
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <div class="text-6xl mb-4">📦</div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No products available</h3>
                        <p class="text-sm">Please add some products first.</p>
                    </div>
                @endforelse
            </div>
        </div>

        @if($products->count() > 0)
            <!-- Order Summary -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Order Summary</h3>
                <div class="flex justify-between items-center text-lg">
                    <span class="font-medium">Total Amount:</span>
                    <span id="totalAmount" class="text-xl font-bold text-green-600">$0.00</span>
                </div>
                <div id="orderSummary" class="mt-2 text-sm text-gray-600"></div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('orders.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    Create Order
                </button>
            </div>
        @endif
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Create Order page loaded');
    
    // Elements
    const quantityInputs = document.querySelectorAll('input[type="number"]');
    const totalAmountElement = document.getElementById('totalAmount');
    const orderSummaryElement = document.getElementById('orderSummary');
    const searchInput = document.getElementById('productSearch');
    const searchBtn = document.getElementById('searchBtn');
    const submitBtn = document.getElementById('submitBtn');
    const productsContainer = document.getElementById('productsContainer');
    const productCount = document.getElementById('productCount');
    
    console.log('Elements found:', {
        quantityInputs: quantityInputs.length,
        searchInput: !!searchInput,
        searchBtn: !!searchBtn,
        submitBtn: !!submitBtn,
        productsContainer: !!productsContainer
    });

    // Calculate total
    function updateTotal() {
        let total = 0;
        let items = [];
        
        quantityInputs.forEach(input => {
            const quantity = parseInt(input.value) || 0;
            const price = parseFloat(input.dataset.price) || 0;
            const productName = input.dataset.productName;
            
            if (quantity > 0) {
                const itemTotal = quantity * price;
                total += itemTotal;
                items.push(`${quantity}x ${productName} = $${itemTotal.toFixed(2)}`);
            }
        });
        
        if (totalAmountElement) {
            totalAmountElement.textContent = '$' + total.toFixed(2);
        }
        if (orderSummaryElement) {
            orderSummaryElement.innerHTML = items.length > 0 ? items.join('<br>') : 'No items selected';
        }
    }

    // Search function
    function performSearch() {
        if (!searchInput || !productsContainer) return;
        
        const searchTerm = searchInput.value.toLowerCase().trim();
        const products = Array.from(productsContainer.children);
        let visibleCount = 0;
        
        products.forEach(product => {
            const allText = product.textContent.toLowerCase();
            const isMatch = searchTerm === '' || allText.includes(searchTerm);
            
            if (isMatch) {
                product.style.display = 'block';
                visibleCount++;
            } else {
                product.style.display = 'none';
            }
        });
        
        // Update count
        if (productCount) {
            if (searchTerm) {
                productCount.innerHTML = `📋 ${visibleCount} product(s) found out of ${products.length} <span class="text-blue-600">(Search: "${searchTerm}")</span>`;
            } else {
                productCount.innerHTML = `📋 ${products.length} product(s) available <span class="ml-4 text-gray-500">💡 Type to search • Click Submit Order or use Ctrl+Enter</span>`;
            }
        }
        
        return visibleCount;
    }

    // Event listeners
    quantityInputs.forEach(input => {
        input.addEventListener('input', updateTotal);
    });

    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(performSearch, 300);
        });
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', performSearch);
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            // Check for selected products
            let hasItems = false;
            let totalItems = 0;
            
            quantityInputs.forEach(input => {
                const quantity = parseInt(input.value) || 0;
                if (quantity > 0) {
                    hasItems = true;
                    totalItems += quantity;
                }
            });
            
            if (!hasItems) {
                alert('⚠️ Please select at least one product before submitting the order.');
                return;
            }
            
            // Confirm submission
            if (confirm(`📋 Submit order with ${totalItems} item(s)?`)) {
                this.innerHTML = '⏳ Submitting...';
                this.disabled = true;
                
                const form = document.querySelector('form');
                if (form) {
                    form.submit();
                }
            }
        });
    }

    // Keyboard shortcut
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.altKey) && e.key === 'Enter') {
            e.preventDefault();
            if (submitBtn) {
                submitBtn.click();
            }
        }
    });

    // Initialize
    updateTotal();
});
</script>
@endsection

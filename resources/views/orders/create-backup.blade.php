@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-navy-900">Create New Order</h2>
            <p class="text-gray-600 mt-1">Add products and create a new order</p>
        </div>
        <a href="{{ route('orders.index') }}" class="btn-secondary">← Back to Orders</a>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4">
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

    @if (session('warning'))
        <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">{{ session('warning') }}</p>
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
            <div class="text-sm text-gray-600 mb-2">
                📋 {{ $products->count() }} product(s) available
                <span class="ml-4 text-gray-500">💡 Type to search • Click Submit Order or use Ctrl+Enter to submit</span>
            </div>
        </div>
        
        <!-- Products Selection -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <label class="block text-sm font-medium text-gray-700">📦 Select Products</label>
              
                </span>
            </div>
            <div class="space-y-4">
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
                                    @if($product->created_at->diffInDays(now()) <= 1)
                                        <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full font-medium animate-pulse">
                                            🔥 Aujourd'hui
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500">SKU: {{ $product->sku }}</p>
                                <p class="text-sm {{ $product->stock_quantity == 0 ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                                    📦 Stock disponible: {{ $product->stock_quantity }} unités
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
                                    name="items[{{ $loop->index }}][quantity]" 
                                    min="0" 
                                    max="{{ $product->stock_quantity == 0 ? 999 : $product->stock_quantity }}"
                                    value="0"
                                    class="w-20 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $product->stock_quantity == 0 ? 'bg-red-50' : '' }}"
                                    data-price="{{ $product->price }}"
                                    data-product-name="{{ $product->name }}"
                                    data-stock="{{ $product->stock_quantity }}"
                                    {{ $product->stock_quantity == 0 ? 'title="Out of stock - Admin can override"' : '' }}
                                >
                                <input type="hidden" name="items[{{ $loop->index }}][product_id]" value="{{ $product->id }}">
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <p>No products available.</p>
                        <a href="{{ route('products.create') }}" class="text-blue-600 hover:text-blue-800">Add some products first</a>
                    </div>
                @endforelse
            </div>
        </div>

        @if($products->count() > 0)
            <!-- Order Notes -->
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Order Notes (Optional)</label>
                <textarea 
                    name="notes" 
                    id="notes" 
                    rows="3" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Special instructions or notes for this order..."
                >{{ old('notes') }}</textarea>
            </div>

            <!-- Order Summary -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-medium text-gray-900">Estimated Total:</span>
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

    // Total calculation
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
        
        totalAmountElement.textContent = '$' + total.toFixed(2);
        orderSummaryElement.innerHTML = items.length > 0 ? items.join('<br>') : 'No items selected';
    }

    quantityInputs.forEach(input => {
        input.addEventListener('input', updateTotal);
    });

    // === FONCTIONNALITÉS RECHERCHE ET SOUMISSION ===

    // Fonction de recherche simple
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const products = Array.from(productsContainer.children);
        let visibleCount = 0;

        products.forEach(product => {
            if (product.id === 'noResultsMessage') return;

            const allText = product.textContent.toLowerCase();
            const isMatch = searchTerm === '' || allText.includes(searchTerm);

            if (isMatch) {
                product.style.display = 'block';
                visibleCount++;
            } else {
                product.style.display = 'none';
            }
        });

        // Mettre à jour le compteur
        const countElement = document.querySelector('.text-sm.text-gray-600');
        if (countElement) {
            if (searchTerm) {
                countElement.innerHTML = `📋 ${visibleCount} product(s) found out of ${products.length}`;
            } else {
                countElement.innerHTML = `📋 ${products.length} product(s) available`;
            }
        }

        return visibleCount;
    }


    // Fonction de recherche réutilisable
    function performSearch(searchTerm = null) {
        const term = (searchTerm || searchInput.value).toLowerCase().trim();
        const products = Array.from(productsContainer.children);

        let visibleCount = 0;
        products.forEach(product => {
            // Ignorer le message "no results"
            if (product.id === 'noResultsMessage') {
                return;
            }

            // Recherche simple dans tout le contenu du produit
            const allText = product.textContent.toLowerCase();
            const isMatch = term === '' || allText.includes(term);

            if (isMatch) {
                product.style.display = 'block';
                visibleCount++;
            } else {
                product.style.display = 'none';
            }
        });

        // Mettre à jour le compteur
        updateProductCount();

        // Afficher message si aucun résultat
        showNoResultsMessage(visibleCount === 0 && term !== '');

        return visibleCount;
    }

    // Recherche en temps réel
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => performSearch(), 300);
    });

    // Recherche avec la touche Entrée dans la barre de recherche
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            performSearch();

            // Effet visuel sur l'input
            this.classList.add('ring-4', 'ring-green-200');
            setTimeout(() => {
                this.classList.remove('ring-4', 'ring-green-200');
            }, 500);
        }
    });

    // Soumission du formulaire avec Ctrl+Enter ou Alt+Enter
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.altKey) && e.key === 'Enter') {
            e.preventDefault();
            console.log('🚀 Raccourci clavier détecté pour soumission');

            // Simuler un clic sur le bouton Submit
            if (submitBtn) {
                submitBtn.click();
            }
        }
    });

    // Bouton de recherche
    if (searchBtn) {
        console.log('✅ Bouton Search trouvé, ajout de l\'event listener');
        searchBtn.addEventListener('click', function() {
            console.log('🔍 Bouton Search cliqué !');
            clearTimeout(searchTimeout);
            const results = performSearch();

            // Feedback visuel sur le bouton
            const originalText = this.innerHTML;
            this.innerHTML = `✅ Found ${results}`;
            this.classList.add('bg-green-600');

            setTimeout(() => {
                this.innerHTML = originalText;
                this.classList.remove('bg-green-600');
            }, 2000);
        });
    } else {
        console.error('❌ Bouton Search non trouvé !');
    }

    // Bouton Submit Order
    if (submitBtn) {
        console.log('✅ Bouton Submit trouvé, ajout de l\'event listener');
        submitBtn.addEventListener('click', function() {
            console.log('📋 Bouton Submit Order cliqué !');

            // Vérifier s'il y a des produits sélectionnés
            const quantityInputs = document.querySelectorAll('input[type="number"]');
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

            // Confirmation avant soumission
            const confirmMessage = `📋 Submit order with ${totalItems} item(s)?`;
            if (confirm(confirmMessage)) {
                console.log('✅ Soumission de la commande confirmée');

                // Feedback visuel
                const originalText = this.innerHTML;
                this.innerHTML = '⏳ Submitting...';
                this.disabled = true;
                this.style.background = '#10b981'; // Vert

                // Soumettre le formulaire
                if (orderForm) {
                    orderForm.submit();
                } else {
                    // Fallback: chercher le formulaire par tag
                    const form = document.querySelector('form');
                    if (form) {
                        form.submit();
                    } else {
                        console.error('❌ Aucun formulaire trouvé !');
                        alert('❌ Error: Could not find form to submit');
                        // Restaurer le bouton en cas d'erreur
                        setTimeout(() => {
                            this.innerHTML = originalText;
                            this.disabled = false;
                            this.style.background = '#f59e0b';
                        }, 2000);
                    }
                }
            } else {
                console.log('❌ Soumission annulée par l\'utilisateur');
            }
        });
    } else {
        console.error('❌ Bouton Submit non trouvé !');
    }



    // Fonction pour mettre à jour le compteur de produits visibles
    function updateProductCount() {
        const products = Array.from(productsContainer.children).filter(p => p.id !== 'noResultsMessage');
        const visibleProducts = products.filter(product => product.style.display !== 'none');
        const countElement = document.querySelector('.text-sm.text-gray-600');

        if (countElement) {
            const searchTerm = searchInput.value.trim();
            if (searchTerm) {
                countElement.innerHTML = `📋 ${visibleProducts.length} product(s) found out of ${products.length} <span class="text-navy-600 font-medium">(Search: "${searchTerm}")</span>`;
            } else {
                countElement.innerHTML = `📋 ${products.length} product(s) available <span class="ml-4 text-gray-500">💡 Type to search in real-time</span>`;
            }
        }
    }

    // Fonction pour afficher/masquer le message "aucun résultat"
    function showNoResultsMessage(show) {
        let noResultsDiv = document.getElementById('noResultsMessage');

        if (show && !noResultsDiv) {
            noResultsDiv = document.createElement('div');
            noResultsDiv.id = 'noResultsMessage';
            noResultsDiv.className = 'text-center py-12 text-gray-500 border-2 border-dashed border-gray-200 rounded-lg';
            noResultsDiv.innerHTML = `
                <div class="text-6xl mb-4">🔍</div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
                <p class="text-sm text-gray-600">Try a different search term to find products.</p>
            `;
            productsContainer.appendChild(noResultsDiv);
        } else if (!show && noResultsDiv) {
            noResultsDiv.remove();
        }
    }

    // Initialiser l'affichage
    updateTotal();
    updateProductCount();
});
</script>
@endsection

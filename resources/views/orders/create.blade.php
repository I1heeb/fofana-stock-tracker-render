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
                <button
                    type="button"
                    id="toggleFiltersBtn"
                    class="px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
                    title="Toggle advanced filters"
                >
                    🔧 Filters
                </button>
            </div>

            <!-- Product count info -->
            <div class="text-sm text-gray-600 mb-2" id="productCount">
                📋 {{ $products->count() }} product(s) available
                <span class="ml-4 text-gray-500">💡 Type to search • Enter bordereau number • Click Submit Order or use Ctrl+Enter to submit</span>
            </div>

            <!-- Collapsible Filters Section -->
            <div id="filtersSection" class="mb-4 hidden">
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-purple-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"></path>
                        </svg>
                        Product Filters
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Stock Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-purple-700 mb-2">Stock Status</label>
                            <select id="stockFilter" class="w-full px-3 py-2 border border-purple-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="">All Products</option>
                                <option value="in_stock">In Stock Only</option>
                                <option value="low_stock">Low Stock</option>
                                <option value="out_of_stock">Out of Stock</option>
                            </select>
                        </div>

                        <!-- Price Range Filter -->
                        <div>
                            <label class="block text-sm font-medium text-purple-700 mb-2">Price Range</label>
                            <select id="priceFilter" class="w-full px-3 py-2 border border-purple-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="">All Prices</option>
                                <option value="0-10">$0 - $10</option>
                                <option value="10-50">$10 - $50</option>
                                <option value="50-100">$50 - $100</option>
                                <option value="100+">$100+</option>
                            </select>
                        </div>

                        <!-- Category Filter -->
                        <div>
                            <label class="block text-sm font-medium text-purple-700 mb-2">Category</label>
                            <select id="categoryFilter" class="w-full px-3 py-2 border border-purple-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="">All Categories</option>
                                <option value="electronics">Electronics</option>
                                <option value="clothing">Clothing</option>
                                <option value="books">Books</option>
                                <option value="home">Home & Garden</option>
                            </select>
                        </div>

                        <!-- Sort By -->
                        <div>
                            <label class="block text-sm font-medium text-purple-700 mb-2">Sort By</label>
                            <select id="sortFilter" class="w-full px-3 py-2 border border-purple-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="name">Name (A-Z)</option>
                                <option value="name_desc">Name (Z-A)</option>
                                <option value="price">Price (Low to High)</option>
                                <option value="price_desc">Price (High to Low)</option>
                                <option value="stock">Stock (Low to High)</option>
                                <option value="stock_desc">Stock (High to Low)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mt-4 pt-4 border-t border-purple-200">
                        <button type="button" id="applyFiltersBtn" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors">
                            ✅ Apply Filters
                        </button>
                        <button type="button" id="clearFiltersBtn" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors">
                            🔄 Clear All Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Products Selection -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <label class="block text-sm font-medium text-gray-700">📦 Select Products</label>
              
                </span>
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
            <!-- Bordereau Number -->
            <div class="mb-6">
                <label for="bordereau_number" class="block text-sm font-medium text-gray-700 mb-2">
                    Bordereau Number <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="bordereau_number"
                    id="bordereau_number"
                    maxlength="12"
                    pattern="\d{12}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('bordereau_number') border-red-500 @enderror"
                    placeholder="Enter 12-digit bordereau number (e.g., 123456789012)"
                    value="{{ old('bordereau_number') }}"
                    required
                >
                @error('bordereau_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Must be exactly 12 digits and unique</p>
            </div>

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

    // Fonction de recherche côté client (rapide et simple)
    function performSearch() {
        if (!searchInput || !productsContainer) {
            console.log('❌ Search elements not found');
            return 0;
        }

        const searchTerm = searchInput.value.toLowerCase().trim();
        const products = Array.from(productsContainer.children);
        let visibleCount = 0;

        console.log(`🔍 Searching for: "${searchTerm}" in ${products.length} products`);

        products.forEach(product => {
            if (product.id === 'noResultsMessage') return;

            // Recherche dans tout le contenu du produit (plus simple et plus fiable)
            const allText = product.textContent.toLowerCase();

            // Recherche spécifique dans le nom du produit pour le highlight
            const productNameElement = product.querySelector('h3');
            const productName = productNameElement ? productNameElement.textContent.toLowerCase() : '';

            // Vérifier si le terme de recherche correspond
            const isMatch = searchTerm === '' || allText.includes(searchTerm);

            if (isMatch) {
                product.style.display = 'block';
                visibleCount++;

                // Highlight du terme recherché dans le nom du produit
                if (searchTerm && productNameElement && productName.includes(searchTerm)) {
                    const originalText = productNameElement.textContent;
                    const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                    const highlightedText = originalText.replace(regex, '<mark class="bg-yellow-200 px-1 rounded">$1</mark>');
                    productNameElement.innerHTML = highlightedText;
                }
            } else {
                product.style.display = 'none';

                // Retirer le highlight si le produit n'est plus visible
                if (productNameElement && productNameElement.innerHTML.includes('<mark')) {
                    productNameElement.innerHTML = productNameElement.textContent;
                }
            }
        });

        // Mettre à jour le compteur de produits
        updateProductCount(visibleCount, products.length, searchTerm);

        // Afficher message si aucun résultat
        showNoResultsMessage(visibleCount === 0 && searchTerm !== '');

        console.log(`✅ Search completed: ${visibleCount}/${products.length} products visible`);
        return visibleCount;
    }

    // Fonction pour mettre à jour le compteur de produits
    function updateProductCount(visibleCount = null, totalCount = null, searchTerm = '') {
        const countElement = document.getElementById('productCount');
        if (!countElement) return;

        if (visibleCount === null) {
            const products = Array.from(productsContainer.children).filter(p => p.id !== 'noResultsMessage');
            totalCount = products.length;
            visibleCount = products.filter(p => p.style.display !== 'none').length;
        }

        if (searchTerm) {
            countElement.innerHTML = `📋 ${visibleCount} product(s) found out of ${totalCount} <span class="text-blue-600">(Search: "${searchTerm}")</span>`;
        } else {
            countElement.innerHTML = `📋 ${totalCount} product(s) available <span class="ml-4 text-gray-500">💡 Type to search • Click Submit Order or use Ctrl+Enter to submit</span>`;
        }
    }

    // Fonction pour afficher/masquer le message "aucun résultat"
    function showNoResultsMessage(show) {
        let noResultsMsg = document.getElementById('noResultsMessage');

        if (show && !noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.id = 'noResultsMessage';
            noResultsMsg.className = 'text-center py-8 text-gray-500 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300';
            noResultsMsg.innerHTML = `
                <div class="text-4xl mb-2">🔍</div>
                <p class="text-lg font-medium">Aucun produit trouvé</p>
                <p class="text-sm">Essayez un autre terme de recherche ou <button onclick="clearSearch()" class="text-blue-600 hover:underline">effacez la recherche</button></p>
            `;
            productsContainer.appendChild(noResultsMsg);
        } else if (!show && noResultsMsg) {
            noResultsMsg.remove();
        }
    }

    // Fonction pour effacer la recherche
    function clearSearch() {
        if (searchInput) {
            searchInput.value = '';
            performSearch();
            searchInput.focus();
        }
    }

    // === ÉVÉNEMENTS DE RECHERCHE ===
    let searchTimeout;

    // Recherche en temps réel avec vérification
    if (searchInput) {
        console.log('✅ Input Search trouvé, ajout des event listeners');

        searchInput.addEventListener('input', function() {
            console.log('🔍 Input Search modifié:', this.value);
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const results = performSearch();
                console.log(`📊 Recherche terminée: ${results} produits trouvés`);
            }, 300);
        });

        // Recherche avec la touche Entrée dans la barre de recherche
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                console.log('🔍 Enter pressed in search input');
                clearTimeout(searchTimeout);
                const results = performSearch();
                console.log(`📊 Recherche Enter: ${results} produits trouvés`);

                // Effet visuel sur l'input
                this.classList.add('ring-4', 'ring-green-200');
                setTimeout(() => {
                    this.classList.remove('ring-4', 'ring-green-200');
                }, 500);
            }
        });

        // Focus automatique sur l'input de recherche
        searchInput.focus();
        console.log('🎯 Focus mis sur l\'input de recherche');
    } else {
        console.error('❌ Input Search non trouvé !');
    }

    // Validation et formatage du numéro de bordereau
    const bordereauInput = document.getElementById('bordereau_number');
    if (bordereauInput) {
        // Permettre seulement les chiffres
        bordereauInput.addEventListener('input', function(e) {
            // Supprimer tout ce qui n'est pas un chiffre
            this.value = this.value.replace(/\D/g, '');

            // Limiter à 12 chiffres
            if (this.value.length > 12) {
                this.value = this.value.substring(0, 12);
            }

            // Validation visuelle
            if (this.value.length === 12) {
                this.classList.remove('border-red-500');
                this.classList.add('border-green-500');
            } else {
                this.classList.remove('border-green-500');
                if (this.value.length > 0) {
                    this.classList.add('border-red-500');
                }
            }
        });

        // Validation lors de la perte de focus
        bordereauInput.addEventListener('blur', function() {
            if (this.value.length > 0 && this.value.length !== 12) {
                this.classList.add('border-red-500');
            }
        });
    }

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

            // Vérifier le numéro de bordereau
            const bordereauInput = document.getElementById('bordereau_number');
            const bordereauNumber = bordereauInput ? bordereauInput.value.trim() : '';

            if (!bordereauNumber) {
                alert('⚠️ Please enter a bordereau number before submitting the order.');
                if (bordereauInput) bordereauInput.focus();
                return;
            }

            if (!/^\d{12}$/.test(bordereauNumber)) {
                alert('⚠️ Bordereau number must be exactly 12 digits.');
                if (bordereauInput) bordereauInput.focus();
                return;
            }

            // Confirmation avant soumission
            const confirmMessage = `📋 Submit order with ${totalItems} item(s)?\nBordereau: ${bordereauNumber}`;
            if (confirm(confirmMessage)) {
                console.log('✅ Soumission de la commande confirmée avec bordereau:', bordereauNumber);

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

    // === GESTION DES FILTRES COLLAPSIBLES ===
    const toggleFiltersBtn = document.getElementById('toggleFiltersBtn');
    const filtersSection = document.getElementById('filtersSection');
    const applyFiltersBtn = document.getElementById('applyFiltersBtn');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');

    // Toggle filters section
    if (toggleFiltersBtn && filtersSection) {
        toggleFiltersBtn.addEventListener('click', function() {
            const isHidden = filtersSection.classList.contains('hidden');

            if (isHidden) {
                filtersSection.classList.remove('hidden');
                filtersSection.style.display = 'block';
                this.innerHTML = '🔧 Hide Filters';
                this.classList.remove('bg-purple-600', 'hover:bg-purple-700');
                this.classList.add('bg-red-600', 'hover:bg-red-700');
            } else {
                filtersSection.classList.add('hidden');
                filtersSection.style.display = 'none';
                this.innerHTML = '🔧 Filters';
                this.classList.remove('bg-red-600', 'hover:bg-red-700');
                this.classList.add('bg-purple-600', 'hover:bg-purple-700');
            }
        });
    }

    // Apply filters
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            applyProductFilters();

            // Visual feedback
            this.innerHTML = '✅ Applied!';
            this.classList.add('bg-green-600');
            setTimeout(() => {
                this.innerHTML = '✅ Apply Filters';
                this.classList.remove('bg-green-600');
            }, 1500);
        });
    }

    // Clear filters
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            clearProductFilters();

            // Visual feedback
            this.innerHTML = '🔄 Cleared!';
            this.classList.add('bg-green-600');
            setTimeout(() => {
                this.innerHTML = '🔄 Clear All Filters';
                this.classList.remove('bg-green-600');
            }, 1500);
        });
    }

    // Function to apply product filters
    function applyProductFilters() {
        const stockFilter = document.getElementById('stockFilter').value;
        const priceFilter = document.getElementById('priceFilter').value;
        const categoryFilter = document.getElementById('categoryFilter').value;
        const sortFilter = document.getElementById('sortFilter').value;

        const products = Array.from(productsContainer.children).filter(p => p.id !== 'noResultsMessage');
        let filteredProducts = [...products];

        // Apply stock filter
        if (stockFilter) {
            filteredProducts = filteredProducts.filter(product => {
                const stockText = product.textContent.toLowerCase();
                switch (stockFilter) {
                    case 'in_stock':
                        return !stockText.includes('out of stock') && !stockText.includes('stock: 0');
                    case 'low_stock':
                        return stockText.includes('low stock');
                    case 'out_of_stock':
                        return stockText.includes('out of stock') || stockText.includes('stock: 0');
                    default:
                        return true;
                }
            });
        }

        // Apply price filter
        if (priceFilter) {
            filteredProducts = filteredProducts.filter(product => {
                const priceText = product.textContent.match(/\$(\d+\.?\d*)/);
                if (!priceText) return true;

                const price = parseFloat(priceText[1]);
                switch (priceFilter) {
                    case '0-10':
                        return price >= 0 && price <= 10;
                    case '10-50':
                        return price > 10 && price <= 50;
                    case '50-100':
                        return price > 50 && price <= 100;
                    case '100+':
                        return price > 100;
                    default:
                        return true;
                }
            });
        }

        // Sort products
        if (sortFilter) {
            filteredProducts.sort((a, b) => {
                switch (sortFilter) {
                    case 'name':
                        return a.querySelector('h3').textContent.localeCompare(b.querySelector('h3').textContent);
                    case 'name_desc':
                        return b.querySelector('h3').textContent.localeCompare(a.querySelector('h3').textContent);
                    case 'price':
                        const priceA = parseFloat(a.textContent.match(/\$(\d+\.?\d*)/)?.[1] || 0);
                        const priceB = parseFloat(b.textContent.match(/\$(\d+\.?\d*)/)?.[1] || 0);
                        return priceA - priceB;
                    case 'price_desc':
                        const priceA2 = parseFloat(a.textContent.match(/\$(\d+\.?\d*)/)?.[1] || 0);
                        const priceB2 = parseFloat(b.textContent.match(/\$(\d+\.?\d*)/)?.[1] || 0);
                        return priceB2 - priceA2;
                    default:
                        return 0;
                }
            });
        }

        // Hide all products first
        products.forEach(product => {
            product.style.display = 'none';
        });

        // Show filtered products
        filteredProducts.forEach(product => {
            product.style.display = 'block';
        });

        // Reorder products in DOM
        filteredProducts.forEach(product => {
            productsContainer.appendChild(product);
        });

        // Update count
        updateProductCount(filteredProducts.length, products.length, 'Filtered');

        console.log(`🔧 Filters applied: ${filteredProducts.length}/${products.length} products shown`);
    }

    // Function to clear all filters
    function clearProductFilters() {
        // Reset all filter selects
        document.getElementById('stockFilter').value = '';
        document.getElementById('priceFilter').value = '';
        document.getElementById('categoryFilter').value = '';
        document.getElementById('sortFilter').value = 'name';

        // Show all products
        const products = Array.from(productsContainer.children).filter(p => p.id !== 'noResultsMessage');
        products.forEach(product => {
            product.style.display = 'block';
        });

        // Update count
        updateProductCount();

        console.log('🔄 All filters cleared');
    }

    // Initialiser l'affichage
    updateTotal();
    updateProductCount();
});
</script>
@endsection

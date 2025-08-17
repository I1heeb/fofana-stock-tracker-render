<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Submit Button</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">🧪 Test Submit Order Button</h1>
        
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Button Tests</h2>
            
            <!-- Test Form -->
            <form id="orderForm" action="#" method="POST" class="mb-6">
                <div class="flex gap-3 mb-4">
                    <div class="flex-1">
                        <input
                            type="text"
                            id="productSearch"
                            placeholder="🔍 Search products by name, SKU, or any text..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <button
                        type="button"
                        id="searchBtn"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        🔍 Search
                    </button>
                    <button
                        type="button"
                        id="submitBtn"
                        class="px-4 py-3 bg-yellow-500 text-blue-900 rounded-lg hover:bg-yellow-600"
                        title="Submit the order (same as pressing Enter)"
                    >
                        📋 Submit Order
                    </button>
                </div>
                
                <!-- Test Products -->
                <div class="space-y-4 mb-6">
                    <div class="border rounded-lg p-4">
                        <h3 class="font-medium">Test Product 1</h3>
                        <p class="text-sm text-gray-600">SKU: TEST-001 | $29.99 | 10 in stock</p>
                        <div class="mt-2">
                            <label class="text-sm font-medium">Quantity:</label>
                            <input type="number" name="products[1][quantity]" min="0" max="10" value="0" class="ml-2 w-20 px-2 py-1 border rounded">
                        </div>
                    </div>
                    
                    <div class="border rounded-lg p-4">
                        <h3 class="font-medium">Test Product 2</h3>
                        <p class="text-sm text-gray-600">SKU: TEST-002 | $19.99 | 5 in stock</p>
                        <div class="mt-2">
                            <label class="text-sm font-medium">Quantity:</label>
                            <input type="number" name="products[2][quantity]" min="0" max="5" value="0" class="ml-2 w-20 px-2 py-1 border rounded">
                        </div>
                    </div>
                </div>
                
                <!-- Traditional Submit Button -->
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Traditional Submit
                </button>
            </form>
        </div>
        
        <!-- Test Results -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Test Results</h2>
            <div id="testResults" class="space-y-2 text-sm">
                <p>🔄 Waiting for tests...</p>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('productSearch');
        const searchBtn = document.getElementById('searchBtn');
        const submitBtn = document.getElementById('submitBtn');
        const orderForm = document.getElementById('orderForm');
        const testResults = document.getElementById('testResults');
        
        function addTestResult(message, success = true) {
            const p = document.createElement('p');
            p.className = success ? 'text-green-600' : 'text-red-600';
            p.innerHTML = (success ? '✅ ' : '❌ ') + message;
            testResults.appendChild(p);
        }
        
        // Test 1: Check if elements exist
        addTestResult('Search Input found: ' + !!searchInput, !!searchInput);
        addTestResult('Search Button found: ' + !!searchBtn, !!searchBtn);
        addTestResult('Submit Button found: ' + !!submitBtn, !!submitBtn);
        addTestResult('Order Form found: ' + !!orderForm, !!orderForm);
        
        // Test 2: Search Button functionality
        if (searchBtn) {
            searchBtn.addEventListener('click', function() {
                addTestResult('Search Button clicked successfully');
                const searchTerm = searchInput.value;
                addTestResult('Search term: "' + searchTerm + '"');
            });
        }
        
        // Test 3: Submit Button functionality
        if (submitBtn) {
            submitBtn.addEventListener('click', function() {
                addTestResult('Submit Button clicked successfully');
                
                // Check for selected products
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
                    addTestResult('Validation: No products selected');
                    alert('⚠️ Please select at least one product before submitting the order.');
                    return;
                }
                
                addTestResult('Validation: ' + totalItems + ' items selected');
                
                // Confirmation
                const confirmMessage = `📋 Submit order with ${totalItems} item(s)?`;
                if (confirm(confirmMessage)) {
                    addTestResult('User confirmed submission');
                    
                    // Simulate form submission
                    this.innerHTML = '⏳ Submitting...';
                    this.disabled = true;
                    this.style.background = '#10b981';
                    
                    setTimeout(() => {
                        addTestResult('Form submission simulated (prevented actual submission)');
                        this.innerHTML = '📋 Submit Order';
                        this.disabled = false;
                        this.style.background = '#eab308';
                    }, 2000);
                } else {
                    addTestResult('User cancelled submission');
                }
            });
        }
        
        // Test 4: Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.altKey) && e.key === 'Enter') {
                e.preventDefault();
                addTestResult('Keyboard shortcut detected: ' + (e.ctrlKey ? 'Ctrl' : 'Alt') + '+Enter');
                if (submitBtn) {
                    submitBtn.click();
                }
            }
        });
        
        // Test 5: Search functionality
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const searchTerm = this.value.toLowerCase();
                    addTestResult('Real-time search: "' + searchTerm + '"');
                }, 300);
            });
        }
        
        addTestResult('All event listeners attached successfully');
    });
    </script>
</body>
</html>

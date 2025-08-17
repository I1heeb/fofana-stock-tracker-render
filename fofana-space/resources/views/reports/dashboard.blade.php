<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Total Orders</div>
                        <div class="mt-2 text-3xl font-bold" id="total-orders">-</div>
                        <div class="mt-1 text-sm text-gray-500" id="total-orders-period">Last 30 days</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Avg. Fulfillment Time</div>
                        <div class="mt-2 text-3xl font-bold" id="avg-fulfillment">-</div>
                        <div class="mt-1 text-sm text-gray-500">From order to delivery</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Current Stock Value</div>
                        <div class="mt-2 text-3xl font-bold" id="stock-value">-</div>
                        <div class="mt-1 text-sm text-gray-500">Total inventory value</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Low Stock Items</div>
                        <div class="mt-2 text-3xl font-bold" id="low-stock-count">-</div>
                        <div class="mt-1 text-sm text-gray-500">Below threshold</div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-4">Reports Dashboard</h2>

                    <!-- Time Range Filter -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <div>
                                <label for="timeRange" class="block text-sm font-medium text-gray-700">Time Range</label>
                                <select id="timeRange" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="7">Last 7 days</option>
                                    <option value="30" selected>Last 30 days</option>
                                    <option value="90">Last 90 days</option>
                                </select>
                            </div>
                            <div>
                                <label for="groupBy" class="block text-sm font-medium text-gray-700">Group By</label>
                                <select id="groupBy" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="day">Day</option>
                                    <option value="week">Week</option>
                                    <option value="month">Month</option>
                                </select>
                            </div>
                            <div>
                                <label for="productFilter" class="block text-sm font-medium text-gray-700">Products</label>
                                <select id="productFilter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" multiple>
                                    <option value="">Top 5 Products</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end space-x-2">
                                <button type="button" id="updateCharts" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Update Charts
                                </button>
                                <button type="button" id="savePreset" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Save Preset
                                </button>
                            </div>
                        </div>

                        <!-- Saved Presets -->
                        <div class="mt-4" id="savedPresets">
                            <!-- Presets will be dynamically added here -->
                        </div>
                    </div>

                    <!-- Charts Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Orders by Status Chart -->
                        <div class="bg-white rounded-lg shadow p-4">
                            <div class="h-[400px]">
                                <canvas id="ordersChart"></canvas>
                            </div>
                        </div>

                        <!-- Stock Levels Chart -->
                        <div class="bg-white rounded-lg shadow p-4">
                            <div class="h-[400px]">
                                <canvas id="stockChart"></canvas>
                            </div>
                        </div>

                        <!-- Low Stock Alerts Chart -->
                        <div class="bg-white rounded-lg shadow p-4 lg:col-span-2">
                            <div class="h-[300px]">
                                <canvas id="lowStockChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    @vite(['resources/js/charts.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let ordersChart = null;
            let stockChart = null;
            let lowStockChart = null;

            // Load saved presets from localStorage
            function loadSavedPresets() {
                const presets = JSON.parse(localStorage.getItem('dashboardPresets') || '[]');
                const container = document.getElementById('savedPresets');
                container.innerHTML = '';

                if (presets.length > 0) {
                    const presetList = document.createElement('div');
                    presetList.className = 'flex flex-wrap gap-2';
                    
                    presets.forEach((preset, index) => {
                        const presetButton = document.createElement('button');
                        presetButton.className = 'inline-flex items-center px-3 py-1 bg-gray-100 text-sm text-gray-700 rounded-full hover:bg-gray-200';
                        presetButton.innerHTML = `
                            ${preset.name}
                            <svg class="h-4 w-4 ml-1 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" onclick="event.stopPropagation(); deletePreset(${index})">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        `;
                        presetButton.onclick = () => loadPreset(preset);
                        presetList.appendChild(presetButton);
                    });

                    container.appendChild(presetList);
                }
            }

            // Save current filters as a preset
            document.getElementById('savePreset').onclick = () => {
                const name = prompt('Enter a name for this preset:');
                if (!name) return;

                const preset = {
                    name,
                    timeRange: document.getElementById('timeRange').value,
                    groupBy: document.getElementById('groupBy').value,
                    productIds: Array.from(document.getElementById('productFilter').selectedOptions).map(opt => opt.value)
                };

                const presets = JSON.parse(localStorage.getItem('dashboardPresets') || '[]');
                presets.push(preset);
                localStorage.setItem('dashboardPresets', JSON.stringify(presets));
                loadSavedPresets();
            };

            // Load a preset
            function loadPreset(preset) {
                document.getElementById('timeRange').value = preset.timeRange;
                document.getElementById('groupBy').value = preset.groupBy;
                
                const productFilter = document.getElementById('productFilter');
                Array.from(productFilter.options).forEach(option => {
                    option.selected = preset.productIds.includes(option.value);
                });

                updateCharts();
            }

            // Delete a preset
            window.deletePreset = function(index) {
                const presets = JSON.parse(localStorage.getItem('dashboardPresets') || '[]');
                presets.splice(index, 1);
                localStorage.setItem('dashboardPresets', JSON.stringify(presets));
                loadSavedPresets();
            };

            async function updateKPIs() {
                const timeRange = document.getElementById('timeRange').value;
                try {
                    const response = await fetch(`/charts/kpis?days=${timeRange}`);
                    const data = await response.json();

                    document.getElementById('total-orders').textContent = data.total_orders;
                    document.getElementById('avg-fulfillment').textContent = formatDuration(data.avg_fulfillment_hours);
                    document.getElementById('stock-value').textContent = formatCurrency(data.stock_value);
                    document.getElementById('low-stock-count').textContent = data.low_stock_count;
                    document.getElementById('total-orders-period').textContent = `Last ${timeRange} days`;
                } catch (error) {
                    console.error('Error fetching KPIs:', error);
                }
            }

            async function fetchChartData() {
                const timeRange = document.getElementById('timeRange').value;
                const groupBy = document.getElementById('groupBy').value;
                const productIds = Array.from(document.getElementById('productFilter').selectedOptions).map(opt => opt.value).filter(Boolean);

                try {
                    // Fetch orders by status data
                    const ordersResponse = await fetch(`/charts/orders-by-status?days=${timeRange}&group_by=${groupBy}`);
                    const ordersData = await ordersResponse.json();

                    // Fetch stock levels data
                    const stockResponse = await fetch(`/charts/stock-levels?days=${timeRange}&group_by=${groupBy}&product_ids=${productIds.join(',')}`);
                    const stockData = await stockResponse.json();

                    // Fetch low stock alerts data
                    const lowStockResponse = await fetch('/charts/low-stock-alerts');
                    const lowStockData = await lowStockResponse.json();

                    return { ordersData, stockData, lowStockData };
                } catch (error) {
                    console.error('Error fetching chart data:', error);
                    return null;
                }
            }

            async function updateCharts() {
                const data = await fetchChartData();
                if (!data) return;

                // Destroy existing charts
                if (ordersChart) ordersChart.destroy();
                if (stockChart) stockChart.destroy();
                if (lowStockChart) lowStockChart.destroy();

                // Initialize new charts with updated data
                ordersChart = initOrdersChart('ordersChart', data.ordersData);
                stockChart = initStockChart('stockChart', data.stockData);
                lowStockChart = initLowStockChart('lowStockChart', data.lowStockData);

                // Update KPIs
                updateKPIs();
            }

            // Initial load
            loadSavedPresets();
            updateCharts();

            // Update charts when filter changes
            document.getElementById('updateCharts').addEventListener('click', updateCharts);
        });
    </script>
    @endpush
</x-app-layout> 
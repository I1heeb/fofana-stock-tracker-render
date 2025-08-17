<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
    <!-- Orders by Status Chart -->
    <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Orders by Status</h3>
        <div class="h-48">
            <canvas id="ordersStatusChart"></canvas>
        </div>
    </div>

    <!-- Low Stock Items Chart -->
    <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Stock Levels</h3>
        <div class="h-48">
            <canvas id="stockLevelsChart"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Orders by Status Pie Chart
    const ordersCtx = document.getElementById('ordersStatusChart').getContext('2d');
    new Chart(ordersCtx, {
        type: 'pie',
        data: {
            labels: ['Pending', 'In Progress', 'Packed', 'Out', 'Completed'],
            datasets: [{
                data: [{{ $stats['pending_orders'] }}, 15, 8, 12, 45],
                backgroundColor: [
                    '#fbbf24', '#3b82f6', '#8b5cf6', '#10b981', '#22c55e'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Stock Levels Bar Chart
    const stockCtx = document.getElementById('stockLevelsChart').getContext('2d');
    new Chart(stockCtx, {
        type: 'bar',
        data: {
            labels: ['Normal', 'Low Stock', 'Out of Stock'],
            datasets: [{
                label: 'Products',
                data: [{{ $stats['total_products'] - $stats['low_stock_products'] }}, {{ $stats['low_stock_products'] }}, 5],
                backgroundColor: ['#22c55e', '#fbbf24', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
@endpush
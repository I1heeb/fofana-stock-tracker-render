// Simple polling approach (no Pusher needed)
export function initDashboardRealTime() {
    // Poll for updates every 30 seconds
    setInterval(() => {
        updateDashboardData();
    }, 30000);
}

function updateDashboardData() {
    fetch('/api/dashboard/recent-orders')
        .then(response => response.json())
        .then(data => {
            updateRecentOrdersList(data.recent_orders);
            updateOrderStats(data.stats);
        })
        .catch(error => console.log('Dashboard update failed:', error));
}

function updateRecentOrdersList(orders) {
    const ordersList = document.querySelector('#recent-orders-list');
    if (!ordersList) return;

    ordersList.innerHTML = orders.map(order => `
        <div class="flex justify-between items-center py-3 border-b last:border-none hover:bg-gray-50 transition-colors" data-order-id="${order.id}">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusBadgeClass(order.status)}">
                        ${order.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                    </span>
                </div>
                <div>
                    <a href="/orders/${order.id}" class="text-sm font-medium text-gray-900 hover:text-blue-600">
                        Order #${order.id}
                    </a>
                    <p class="text-sm text-gray-500">by ${order.user_name}</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500">${order.total_items} items</div>
                <div class="text-xs text-gray-400">${order.created_at}</div>
            </div>
        </div>
    `).join('');
}

function updateOrderInList(order) {
    const orderElement = document.querySelector(`[data-order-id="${order.id}"]`);
    if (orderElement) {
        const statusBadge = orderElement.querySelector('[role="status"]');
        if (statusBadge) {
            statusBadge.textContent = order.status;
            statusBadge.setAttribute('aria-label', `Order ${order.status}`);
            // Update badge color based on status
            statusBadge.className = `text-xs px-2 py-1 rounded ml-2 ${getStatusBadgeClass(order.status)}`;
        }
        orderElement.classList.add('pulse-animation');
    }
}

function getStatusBadgeClass(status) {
    const classes = {
        'in_progress': 'bg-blue-100 text-blue-800',
        'packed': 'bg-yellow-100 text-yellow-800',
        'out': 'bg-green-100 text-green-800',
        'canceled': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function updateOrderStats(stats) {
    document.querySelector('#total-orders').textContent = stats.total_orders;
    document.querySelector('#pending-orders').textContent = stats.pending_orders;
}

function showPulseAnimation() {
    const elements = document.querySelectorAll('.pulse-animation');
    elements.forEach(el => {
        setTimeout(() => el.classList.remove('pulse-animation'), 2000);
    });
}


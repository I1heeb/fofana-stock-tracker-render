import Chart from 'chart.js/auto';
import { createFocusTrap } from 'focus-trap';

// Utility function to format dates for charts
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    });
};

// Utility function to format currency
const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(value);
};

// Utility function to format duration
const formatDuration = (hours) => {
    if (hours < 1) return Math.round(hours * 60) + ' mins';
    if (hours < 24) return Math.round(hours) + ' hours';
    return Math.round(hours / 24) + ' days';
};

// Common chart options
const commonOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
        },
        tooltip: {
            mode: 'index',
            intersect: false
        },
        zoom: {
            zoom: {
                wheel: {
                    enabled: true,
                },
                pinch: {
                    enabled: true
                },
                mode: 'x',
            },
            pan: {
                enabled: true,
                mode: 'x',
            }
        }
    }
};

// Initialize Orders by Status chart
export function initOrdersChart(elementId, data) {
    const ctx = document.getElementById(elementId).getContext('2d');
    
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.dates,
            datasets: data.datasets
        },
        options: {
            ...commonOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                ...commonOptions.plugins,
                title: {
                    display: true,
                    text: 'Orders by Status Over Time'
                },
                annotation: {
                    annotations: {
                        anomalyLine: {
                            type: 'line',
                            yMin: data.anomalyThreshold,
                            yMax: data.anomalyThreshold,
                            borderColor: 'rgba(255, 99, 132, 0.5)',
                            borderWidth: 2,
                            borderDash: [6, 6],
                            label: {
                                content: 'Anomaly Threshold',
                                display: true
                            }
                        }
                    }
                }
            },
            onClick: async (event, elements) => {
                if (elements.length > 0) {
                    const element = elements[0];
                    const date = data.dates[element.index];
                    const status = data.datasets[element.datasetIndex].label.toLowerCase().replace(' ', '_');
                    
                    try {
                        const response = await fetch(`/charts/drilldown?date=${date}&status=${status}`);
                        const drilldownData = await response.json();
                        showDrilldownModal(date, status, { status });
                    } catch (error) {
                        console.error('Error fetching drilldown data:', error);
                    }
                }
            }
        }
    });

    // Add export button
    addExportButton(elementId, chart, 'orders-by-status');

    return chart;
}

// Initialize Stock Levels chart
export function initStockChart(elementId, data) {
    const ctx = document.getElementById(elementId).getContext('2d');
    
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.dates,
            datasets: data.datasets
        },
        options: {
            ...commonOptions,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                ...commonOptions.plugins,
                title: {
                    display: true,
                    text: 'Stock Levels Over Time'
                }
            },
            onClick: async (event, elements) => {
                if (elements.length > 0) {
                    const element = elements[0];
                    const date = data.dates[element.index];
                    const productId = data.datasets[element.datasetIndex].productId;
                    
                    try {
                        const response = await fetch(`/charts/drilldown?date=${date}&product_id=${productId}`);
                        const drilldownData = await response.json();
                        showDrilldownModal(date, 'stock changes', { productId });
                    } catch (error) {
                        console.error('Error fetching drilldown data:', error);
                    }
                }
            }
        }
    });

    // Add export button
    addExportButton(elementId, chart, 'stock-levels');

    return chart;
}

// Initialize Low Stock Alerts chart
export function initLowStockChart(elementId, data) {
    const ctx = document.getElementById(elementId).getContext('2d');
    
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.products,
            datasets: [{
                label: 'Current Stock',
                data: data.currentStock,
                backgroundColor: data.colors,
                borderColor: data.borderColors,
                borderWidth: 1
            }, {
                label: 'Threshold',
                data: data.thresholds,
                type: 'line',
                borderColor: 'rgba(75, 85, 99, 0.5)',
                borderWidth: 2,
                borderDash: [6, 6],
                fill: false,
                pointStyle: 'dash'
            }]
        },
        options: {
            ...commonOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Stock Level'
                    }
                }
            },
            plugins: {
                ...commonOptions.plugins,
                title: {
                    display: true,
                    text: 'Low Stock Products'
                }
            }
        }
    });

    // Add export button
    addExportButton(elementId, chart, 'low-stock');

    return chart;
}

// Utility function to add export button
function addExportButton(elementId, chart, filename) {
    const container = document.getElementById(elementId).parentElement;
    const buttonContainer = document.createElement('div');
    buttonContainer.className = 'absolute top-2 right-2 flex space-x-2';

    // Image export button
    const imageButton = document.createElement('button');
    imageButton.className = 'px-2 py-1 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500';
    imageButton.innerHTML = 'Export PNG';
    imageButton.onclick = () => {
        const link = document.createElement('a');
        link.download = `${filename}-${new Date().toISOString().split('T')[0]}.png`;
        link.href = chart.toBase64Image();
        link.click();
    };

    // CSV export button
    const csvButton = document.createElement('button');
    csvButton.className = 'px-2 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500';
    csvButton.innerHTML = 'Export CSV';
    csvButton.onclick = () => {
        const csvContent = generateCSV(chart);
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `${filename}-${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
    };

    buttonContainer.appendChild(imageButton);
    buttonContainer.appendChild(csvButton);
    container.style.position = 'relative';
    container.appendChild(buttonContainer);
}

// Utility function to generate CSV from chart data
function generateCSV(chart) {
    const headers = ['Date'];
    chart.data.datasets.forEach(dataset => {
        headers.push(dataset.label);
    });

    const rows = [headers.join(',')];
    
    chart.data.labels.forEach((label, i) => {
        const row = [label];
        chart.data.datasets.forEach(dataset => {
            row.push(dataset.data[i]);
        });
        rows.push(row.join(','));
    });

    return rows.join('\n');
}

// Function to show drill-down modal
async function showDrilldownModal(date, type, params = {}) {
    // Remove existing modal if any
    const existingModal = document.getElementById('drilldown-modal');
    if (existingModal) existingModal.remove();

    // Store the element that triggered the modal
    const triggerElement = document.activeElement;

    // Default parameters
    const defaultParams = {
        page: 1,
        per_page: 10,
        sort_by: 'created_at',
        sort_dir: 'desc',
        ...params
    };

    // Fetch data with current parameters
    const queryParams = new URLSearchParams({
        date,
        ...(type === 'stock changes' ? { product_id: params.productId } : { status: type }),
        ...defaultParams
    });

    try {
        const response = await fetch(`/charts/drilldown?${queryParams}`);
        const { data, meta } = await response.json();

        // Create modal container
        const modal = document.createElement('div');
        modal.id = 'drilldown-modal';
        modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center';
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('aria-labelledby', 'modal-title');
        
        // Add live region for announcements
        const liveRegion = document.createElement('div');
        liveRegion.id = 'drilldown-status';
        liveRegion.className = 'sr-only';
        liveRegion.setAttribute('aria-live', 'polite');
        modal.appendChild(liveRegion);

        // Generate pagination controls
        const paginationHtml = generatePaginationControls(meta);

        // Generate sorting controls
        const sortingHtml = `
            <div class="flex items-center space-x-4 mb-4">
                <select id="sort-by" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" aria-label="Sort by field">
                    <option value="created_at" ${defaultParams.sort_by === 'created_at' ? 'selected' : ''}>Date</option>
                    <option value="id" ${defaultParams.sort_by === 'id' ? 'selected' : ''}>Order ID</option>
                    <option value="total_items" ${defaultParams.sort_by === 'total_items' ? 'selected' : ''}>Total Items</option>
                    <option value="total_value" ${defaultParams.sort_by === 'total_value' ? 'selected' : ''}>Total Value</option>
                </select>
                <select id="sort-dir" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" aria-label="Sort direction">
                    <option value="asc" ${defaultParams.sort_dir === 'asc' ? 'selected' : ''}>Ascending</option>
                    <option value="desc" ${defaultParams.sort_dir === 'desc' ? 'selected' : ''}>Descending</option>
                </select>
                <button onclick="applySort()" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Apply Sort
                </button>
            </div>
        `;

        modal.innerHTML += `
            <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4" role="document">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 id="modal-title" class="text-xl font-semibold">
                            Details for ${type} on ${date}
                        </h2>
                        <button 
                            class="text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" 
                            onclick="closeModal()"
                            aria-label="Close modal">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    ${sortingHtml}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="drilldown-table">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                ${data.map(item => `
                                    <tr tabindex="0" class="hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.id}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.date}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.status}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.details}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    ${paginationHtml}
                </div>
            </div>
        `;

        // Add modal to body
        document.body.appendChild(modal);

        // Set up focus trap
        const focusTrap = createFocusTrap(modal, {
            onActivate: () => {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                announce('Drill-down modal opened');
            },
            onDeactivate: () => {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                triggerElement.focus();
                announce('Drill-down modal closed');
            },
            initialFocus: '#sort-by',
            fallbackFocus: modal,
            escapeDeactivates: true,
        });

        // Activate focus trap
        focusTrap.activate();

        // Set up keyboard navigation for table rows
        setupTableNavigation();

        // Store focus trap instance for cleanup
        modal.focusTrap = focusTrap;

    } catch (error) {
        console.error('Error fetching drilldown data:', error);
        announce('Error loading drill-down data. Please try again.');
    }
}

// Function to close modal
function closeModal() {
    const modal = document.getElementById('drilldown-modal');
    if (modal && modal.focusTrap) {
        modal.focusTrap.deactivate();
        modal.remove();
    }
}

// Function to announce messages to screen readers
function announce(message) {
    const status = document.getElementById('drilldown-status');
    if (status) {
        status.textContent = '';
        setTimeout(() => status.textContent = message, 100);
    }
}

// Function to set up keyboard navigation for table rows
function setupTableNavigation() {
    const table = document.getElementById('drilldown-table');
    if (!table) return;

    const rows = table.querySelectorAll('tbody tr');
    rows.forEach((row, idx) => {
        row.addEventListener('keydown', e => {
            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    rows[(idx + 1) % rows.length]?.focus();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    rows[(idx - 1 + rows.length) % rows.length]?.focus();
                    break;
                case 'Enter':
                case ' ':
                    e.preventDefault();
                    // Trigger row action if needed
                    break;
            }
        });
    });
}

// Generate pagination controls
function generatePaginationControls(meta) {
    const pages = [];
    const currentPage = meta.current_page;
    const totalPages = meta.total_pages;

    // Always show first page
    pages.push(1);

    // Show pages around current page
    for (let i = Math.max(2, currentPage - 2); i <= Math.min(totalPages - 1, currentPage + 2); i++) {
        pages.push(i);
    }

    // Always show last page
    if (totalPages > 1) {
        pages.push(totalPages);
    }

    // Add ellipsis where needed
    const paginationItems = [];
    let previousPage = 0;

    pages.forEach(page => {
        if (page - previousPage > 1) {
            paginationItems.push('<span class="px-3 py-2">...</span>');
        }
        paginationItems.push(`
            <button 
                onclick="changePage(${page})" 
                class="px-3 py-2 rounded-md ${page === currentPage ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-50'}"
                ${page === currentPage ? 'aria-current="page"' : ''}
            >
                ${page}
            </button>
        `);
        previousPage = page;
    });

    return `
        <div class="flex items-center justify-between mt-4">
            <div class="flex items-center">
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium">${(currentPage - 1) * meta.per_page + 1}</span> to 
                    <span class="font-medium">${Math.min(currentPage * meta.per_page, meta.total)}</span> of 
                    <span class="font-medium">${meta.total}</span> results
                </p>
            </div>
            <div class="flex items-center space-x-2">
                ${paginationItems.join('')}
            </div>
        </div>
    `;
}

// Get status color for badges
function getStatusColor(status) {
    const colors = {
        'In Progress': 'blue',
        'Packed': 'green',
        'Out': 'yellow',
        'Canceled': 'red',
        'Returned': 'gray'
    };
    return colors[status] || 'gray';
}

// Setup drill-down controls
function setupDrilldownControls(date, type, params) {
    window.changePage = async (page) => {
        await showDrilldownModal(date, type, { ...params, page });
    };

    window.applySort = async () => {
        const sortBy = document.getElementById('sort-by').value;
        const sortDir = document.getElementById('sort-dir').value;
        await showDrilldownModal(date, type, { ...params, sort_by: sortBy, sort_dir: sortDir, page: 1 });
    };

    window.exportDrilldownData = async (format) => {
        const queryParams = new URLSearchParams({
            date,
            ...(type === 'stock changes' ? { product_id: params.productId } : { status: type }),
            format
        });

        try {
            const response = await fetch(`/charts/drilldown/export?${queryParams}`);
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `drilldown-${type}-${date}.${format}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } catch (error) {
            console.error('Error exporting drill-down data:', error);
        }
    };
}

// Export utility functions
export { formatDate, formatCurrency, formatDuration }; 

// Export functions for use in other files
export {
    showDrilldownModal,
    closeModal,
    announce
}; 
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-4">Orders Report</h2>

                    <!-- Filters -->
                    <form method="GET" action="{{ route('reports.orders') }}" class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Statuses</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="from" class="block text-sm font-medium text-gray-700">From Date</label>
                                <input type="date" name="from" id="from" value="{{ request('from') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="to" class="block text-sm font-medium text-gray-700">To Date</label>
                                <input type="date" name="to" id="to" value="{{ request('to') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="product" class="block text-sm font-medium text-gray-700">Product Name/SKU</label>
                                <input type="text" name="product" id="product" value="{{ request('product') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Search by name or SKU">
                            </div>
                        </div>

                        <div class="mt-4 flex justify-between items-center">
                            <div>
                                <label for="per_page" class="text-sm font-medium text-gray-700">Items per page:</label>
                                <select name="per_page" id="per_page" class="ml-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach([20, 50, 100] as $perPage)
                                        <option value="{{ $perPage }}" {{ request('per_page') == $perPage ? 'selected' : '' }}>
                                            {{ $perPage }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex space-x-2">
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                    </svg>
                                    Filter
                                </button>

                                <a href="{{ route('reports.orders.export', request()->query()) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Export CSV
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Bulk Actions (Only visible for packaging role) -->
                    @can('update-orders')
                    <div id="bulk-actions" class="mb-4 p-4 bg-gray-50 rounded-lg hidden">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="mr-2 text-sm font-medium text-gray-700">With selected:</span>
                                <select id="bulk-status" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Choose status...</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                    @endforeach
                                </select>
                                <button type="button" id="bulk-update" class="ml-2 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Update Status
                                </button>
                            </div>
                            <div class="text-sm text-gray-500">
                                <span id="selected-count">0</span> orders selected
                            </div>
                        </div>
                    </div>
                    @endcan

                    <!-- Results -->
                    <div class="mt-4 flex flex-col">
                        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                @can('update-orders')
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    <input type="checkbox" id="select-all" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                </th>
                                                @endcan
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Items</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated At</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse($orders as $order)
                                                <tr>
                                                    @can('update-orders')
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="order-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                    </td>
                                                    @endcan
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ $order->id }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                            @if($order->status === 'in_progress') bg-yellow-100 text-yellow-800
                                                            @elseif($order->status === 'packed') bg-blue-100 text-blue-800
                                                            @elseif($order->status === 'out') bg-green-100 text-green-800
                                                            @elseif($order->status === 'canceled') bg-red-100 text-red-800
                                                            @else bg-gray-100 text-gray-800
                                                            @endif">
                                                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $order->user->email }}
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-500">
                                                        <div class="max-w-xs truncate">
                                                            {{ $order->orderItems->map(function($item) {
                                                                return $item->product->name . ' (x' . $item->quantity . ')';
                                                            })->join(', ') }}
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $order->orderItems->sum('quantity') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $order->created_at->format('Y-m-d H:i:s') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $order->updated_at->format('Y-m-d H:i:s') }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="@can('update-orders')8@else7@endcan" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                        No orders found matching the criteria.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $orders->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('update-orders')
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('select-all');
            const orderCheckboxes = document.querySelectorAll('.order-checkbox');
            const bulkActions = document.getElementById('bulk-actions');
            const selectedCount = document.getElementById('selected-count');
            const bulkUpdate = document.getElementById('bulk-update');
            const bulkStatus = document.getElementById('bulk-status');

            function updateSelectedCount() {
                const count = document.querySelectorAll('.order-checkbox:checked').length;
                selectedCount.textContent = count;
                bulkActions.classList.toggle('hidden', count === 0);
            }

            selectAll.addEventListener('change', function() {
                orderCheckboxes.forEach(checkbox => checkbox.checked = this.checked);
                updateSelectedCount();
            });

            orderCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(orderCheckboxes).every(c => c.checked);
                    selectAll.checked = allChecked;
                    updateSelectedCount();
                });
            });

            bulkUpdate.addEventListener('click', async function() {
                const selectedOrders = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
                const status = bulkStatus.value;

                if (!status) {
                    alert('Please select a status');
                    return;
                }

                if (!selectedOrders.length) {
                    alert('Please select at least one order');
                    return;
                }

                try {
                    const response = await fetch('{{ route('orders.bulk-update') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            order_ids: selectedOrders,
                            status: status
                        })
                    });

                    const result = await response.json();

                    if (response.ok) {
                        alert(result.message);
                        window.location.reload();
                    } else {
                        throw new Error(result.message || 'Failed to update orders');
                    }
                } catch (error) {
                    alert(error.message);
                }
            });
        });
    </script>
    @endpush
    @endcan
</x-app-layout> 
@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-navy-900">All Orders</h2>
            <p class="text-gray-600 mt-1">Manage and track all orders</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('orders.pending') }}" class="btn-secondary">
                Processing Orders
            </a>
            @if(auth()->user()->isAdmin() || auth()->user()->isPackagingAgent())
                <a href="{{ route('orders.create') }}" class="btn-primary">
                    + New Order
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif
    
    <!-- Search Filters -->
    <div class="modern-card p-6">
        <!-- Bootstrap Collapse Header -->
        <div class="mb-4">
            <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center w-100 text-start" 
                    type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#searchFilters" 
                    aria-expanded="true" 
                    aria-controls="searchFilters">
                <svg class="h-5 w-5 text-blue-600 me-2 transition-transform" id="filterIcon" style="transform: rotate(180deg);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
                <h3 class="text-lg font-semibold text-navy-900 mb-0">🔍 Search Filters</h3>
            </button>
        </div>
        
        <!-- Collapsible Content -->
        <div class="collapse show" id="searchFilters">
            <form method="GET" action="{{ route('orders.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                    <!-- Order Number Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Order Number</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            value="{{ request('search') }}"
                            placeholder="Ex: ORD-001"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>

                    <!-- Bordereau Search -->
                    <div>
                        <label for="bordereau_search" class="block text-sm font-medium text-gray-700 mb-1">Bordereau Number</label>
                        <input
                            type="text"
                            name="bordereau_search"
                            id="bordereau_search"
                            value="{{ request('bordereau_search') }}"
                            placeholder="12-digit bordereau..."
                            maxlength="12"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select
                            name="status"
                            id="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                            <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>En traitement</option>
                            <option value="packed" {{ request('status') === 'packed' ? 'selected' : '' }}>Emballé</option>
                            <option value="out" {{ request('status') === 'out' ? 'selected' : '' }}>Expédié</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Terminé</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                            <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Retourné</option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input
                            type="date"
                            name="date_from"
                            id="date_from"
                            value="{{ request('date_from') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>

                    <!-- Date To -->
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input
                            type="date"
                            name="date_to"
                            id="date_to"
                            value="{{ request('date_to') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>

                    <!-- User Filter -->
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">User</label>
                        <select
                            name="user_id"
                            id="user_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">All Users</option>
                            @if(isset($users))
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <!-- Boutons d'action -->
                <div class="flex flex-wrap gap-3 pt-4">
                    <button
                        type="submit"
                        class="btn-primary"
                    >
                        🔍 Search
                    </button>
                    <a
                        href="{{ route('orders.index') }}"
                        class="btn-secondary"
                    >
                        🔄 Reset
                    </a>
                    @if(isset($orders) && $orders->count() > 0)
                        <button
                            type="button"
                            onclick="exportResults()"
                            class="btn-success"
                        >
                            📊 Export ({{ $orders->total() }})
                        </button>
                    @endif
                    @if(request()->hasAny(['search', 'status', 'date_from', 'date_to', 'user_id', 'bordereau_search']))
                        <div class="text-sm text-gray-600 flex items-center">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                {{ isset($orders) ? $orders->total() : 0 }} résultat(s) trouvé(s)
                            </span>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="modern-card overflow-hidden">
        @if($orders->count() > 0)
            <ul class="divide-y divide-gray-200">
                @foreach($orders as $order)
                    <li class="px-6 py-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-navy-600 truncate">
                                        {{ $order->order_number ?? 'ORD-' . str_pad($order->id, 4, '0', STR_PAD_LEFT) }}
                                    </p>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($order->status === 'completed') bg-green-100 text-green-800
                                            @elseif($order->status === 'out') bg-blue-100 text-blue-800
                                            @elseif($order->status === 'packed') bg-purple-100 text-purple-800
                                            @elseif($order->status === 'processing') bg-orange-100 text-orange-800
                                            @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                            @elseif($order->status === 'returned') bg-gray-100 text-gray-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            @switch($order->status)
                                                @case('pending') En attente @break
                                                @case('processing') En traitement @break
                                                @case('packed') Emballé @break
                                                @case('out') Expédié @break
                                                @case('completed') Terminé @break
                                                @case('cancelled') Annulé @break
                                                @case('returned') Retourné @break
                                                @default {{ ucfirst($order->status) }}
                                            @endswitch
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-900">
                                        👤 Client: {{ $order->user->name ?? 'N/A' }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        💰 Montant: ${{ number_format($order->total_amount ?? 0, 2) }} |
                                        📅 Créé: {{ $order->created_at->format('d/m/Y à H:i') }}
                                        @if($order->updated_at && $order->updated_at != $order->created_at)
                                            | 🔄 Modifié: {{ $order->updated_at->format('d/m/Y à H:i') }}
                                        @endif
                                    </p>
                                    @if($order->bordereau_number)
                                        <p class="text-sm text-blue-600 font-medium">
                                            📋 Bordereau: {{ $order->bordereau_number }}
                                        </p>
                                    @endif
                                    @if($order->orderItems && $order->orderItems->count() > 0)
                                        <p class="text-xs text-gray-400 mt-1">
                                            📦 {{ $order->orderItems->count() }} article(s) -
                                            {{ $order->orderItems->sum('quantity') }} unité(s) au total
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="ml-4 flex-shrink-0 flex space-x-3">
                                <a href="{{ route('orders.show', $order) }}" class="text-navy-600 hover:text-navy-900 font-medium">
                                    👁️ View
                                </a>
                                @can('update', $order)
                                    <a href="{{ route('orders.edit', $order) }}" class="text-mustard-600 hover:text-mustard-700 font-medium">
                                        ✏️ Edit
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $orders->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <div class="text-gray-500">
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No orders found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new order.</p>
                    <div class="mt-6">
                        <a href="{{ route('orders.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Create New Order
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

<script>
function setDateRange(period) {
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
    const today = new Date();

    // Format date to YYYY-MM-DD
    function formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    switch(period) {
        case 'today':
            dateFrom.value = formatDate(today);
            dateTo.value = formatDate(today);
            break;

        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            dateFrom.value = formatDate(yesterday);
            dateTo.value = formatDate(yesterday);
            break;

        case 'week':
            const startOfWeek = new Date(today);
            startOfWeek.setDate(today.getDate() - today.getDay() + 1); // Lundi
            dateFrom.value = formatDate(startOfWeek);
            dateTo.value = formatDate(today);
            break;

        case 'month':
            const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            dateFrom.value = formatDate(startOfMonth);
            dateTo.value = formatDate(today);
            break;

        case 'last30':
            const thirtyDaysAgo = new Date(today);
            thirtyDaysAgo.setDate(today.getDate() - 30);
            dateFrom.value = formatDate(thirtyDaysAgo);
            dateTo.value = formatDate(today);
            break;
    }
}

// Export function
function exportResults() {
    const form = document.querySelector('form');
    const formData = new FormData(form);
    const params = new URLSearchParams();

    // Add all form data to params
    for (let [key, value] of formData.entries()) {
        if (value) params.append(key, value);
    }

    // Add export parameter
    params.append('export', 'csv');

    // Create download link
    const url = '{{ route("orders.index") }}?' + params.toString();
    window.open(url, '_blank');
}

// Rotate arrow icon on collapse toggle
document.getElementById('searchFilters').addEventListener('show.bs.collapse', function () {
    document.getElementById('filterIcon').style.transform = 'rotate(180deg)';
});

document.getElementById('searchFilters').addEventListener('hide.bs.collapse', function () {
    document.getElementById('filterIcon').style.transform = 'rotate(0deg)';
});
</script>


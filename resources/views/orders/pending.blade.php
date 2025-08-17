@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-navy-900">Processing Orders</h2>
            <p class="text-gray-600 mt-1">{{ $orders->total() }} orders currently being processed</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('orders.index') }}" class="btn-secondary">
                All Orders
            </a>
            <a href="{{ route('orders.create') }}" class="btn-primary">
                + New Order
            </a>
        </div>
    </div>

    <!-- Search Filters for Processing Orders -->
    <div class="modern-card p-6 mb-6">
        <h3 class="text-lg font-semibold text-navy-900 mb-4">🔍 Filter Processing Orders</h3>
        <form method="GET" action="{{ route('orders.pending') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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

                <!-- Status Filter (for processing orders) -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Processing Status</label>
                    <select
                        name="status"
                        id="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">All Processing</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>In Progress</option>
                        <option value="packed" {{ request('status') === 'packed' ? 'selected' : '' }}>Packed</option>
                    </select>
                </div>

                <!-- User Filter -->
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                    <select
                        name="user_id"
                        id="user_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">All Customers</option>
                        @php
                            $users = \App\Models\User::orderBy('name')->get();
                        @endphp
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center mt-4">
                <div class="flex space-x-2">
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                    >
                        🔍 Filter
                    </button>
                    <a
                        href="{{ route('orders.pending') }}"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors"
                    >
                        🔄 Clear
                    </a>
                </div>

                @if(request()->hasAny(['search', 'status', 'user_id', 'bordereau_search']))
                    <div class="text-sm text-gray-600">
                        <strong>{{ $orders->total() }} order(s) found</strong>
                    </div>
                @endif
            </div>
        </form>
    </div>

    @if($orders->count() > 0)
            <div class="modern-card overflow-hidden">
                <ul class="divide-y divide-gray-200">
                    @foreach($orders as $order)
                        <li class="px-6 py-4 hover:bg-gradient-to-r hover:from-yellow-50 hover:to-blue-50 transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-blue-700 truncate">
                                            Order #{{ $order->id }}
                                        </p>
                                        <div class="ml-2 flex-shrink-0 flex">
                                            <span class="px-3 inline-flex text-xs leading-5 font-semibold rounded-full shadow-sm
                                                @if($order->status === 'in_progress') bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800
                                                @elseif($order->status === 'packed') bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800
                                                @elseif($order->status === 'pending') bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800
                                                @elseif($order->status === 'processing') bg-gradient-to-r from-purple-100 to-purple-200 text-purple-800
                                                @else bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 @endif">
                                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-sm text-blue-900 font-medium">
                                            Customer: {{ $order->user->name ?? 'N/A' }}
                                        </p>
                                        <p class="text-sm text-blue-600">
                                            Items: {{ $order->orderItems->sum('quantity') }} |
                                            Created: {{ $order->created_at->format('M d, Y H:i') }}
                                        </p>
                                        @if($order->bordereau_number)
                                            <p class="text-sm text-green-600 font-mono bg-green-50 px-2 py-1 rounded inline-block mt-1">
                                                📋 Bordereau: {{ $order->bordereau_number }}
                                            </p>
                                        @endif
                                        <div class="mt-1">
                                            <p class="text-xs text-blue-500 truncate">
                                                Products: {{ $order->orderItems->map(function($item) {
                                                    return $item->product->name . ' (x' . $item->quantity . ')';
                                                })->take(3)->join(', ') }}
                                                @if($order->orderItems->count() > 3)
                                                    <span class="text-yellow-600">... and {{ $order->orderItems->count() - 3 }} more</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4 flex-shrink-0 flex space-x-2">
                                    <a href="{{ route('orders.show', $order) }}" 
                                       class="text-blue-600 hover:text-blue-800 px-3 py-1 rounded-md hover:bg-blue-100 transition-all font-medium">
                                        View
                                    </a>
                                    @can('update', $order)
                                        <a href="{{ route('orders.edit', $order) }}" 
                                           class="text-yellow-600 hover:text-yellow-800 px-3 py-1 rounded-md hover:bg-yellow-100 transition-all font-medium">
                                            Edit
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                
                <div class="px-6 py-4 border-t border-blue-200 bg-gradient-to-r from-blue-50 to-yellow-50">
                    {{ $orders->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-16 bg-gradient-to-br from-blue-100 via-white to-yellow-100 rounded-xl shadow-xl border border-blue-200">
                <svg class="mx-auto h-16 w-16 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2m-6 9l2 2 4-4"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-blue-900">No processing orders</h3>
                <p class="mt-2 text-sm text-blue-600">All orders have been completed or are awaiting stock.</p>
                <div class="mt-8">
                    <a href="{{ route('orders.create') }}" 
                       class="inline-flex items-center px-6 py-3 border border-transparent shadow-lg text-sm font-medium rounded-lg text-white bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Create New Order
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
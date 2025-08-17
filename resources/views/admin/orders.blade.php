@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">📋 Order Management</h1>
            <p class="text-gray-600 mt-2">Monitor and manage all system orders</p>
        </div>
        <a href="{{ route('admin.users') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
            ← Back to Admin Dashboard
        </a>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">All Orders ({{ $orders->total() }})</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">Order #{{ $order->id }}</div>
                                    <div class="text-sm text-gray-500">{{ $order->items_count ?? 0 }} items</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-xs font-medium text-gray-700">
                                                {{ substr($order->user->name ?? 'U', 0, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $order->user->name ?? 'Unknown User' }}</div>
                                        <div class="text-sm text-gray-500">{{ $order->user->email ?? 'No email' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    @if($order->status === 'completed') bg-green-100 text-green-800
                                    @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    @if($order->status === 'completed') ✅ Completed
                                    @elseif($order->status === 'pending') ⏳ Pending
                                    @elseif($order->status === 'processing') 🔄 Processing
                                    @elseif($order->status === 'cancelled') ❌ Cancelled
                                    @else 📋 {{ ucfirst($order->status) }} @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-green-600">${{ number_format($order->total_amount, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>{{ $order->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $order->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:text-blue-900 text-xs bg-blue-100 px-2 py-1 rounded">
                                        View
                                    </a>
                                    @if($order->status === 'pending')
                                        <button class="text-green-600 hover:text-green-900 text-xs bg-green-100 px-2 py-1 rounded">
                                            Process
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No orders found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <!-- Order Status Summary -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="text-3xl text-yellow-500">⏳</div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-yellow-900">Pending</h3>
                    <p class="text-2xl font-bold text-yellow-600">
                        {{ $orders->where('status', 'pending')->count() }}
                    </p>
                    <p class="text-sm text-yellow-600">Awaiting processing</p>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="text-3xl text-blue-500">🔄</div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-blue-900">Processing</h3>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ $orders->where('status', 'processing')->count() }}
                    </p>
                    <p class="text-sm text-blue-600">Being processed</p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="text-3xl text-green-500">✅</div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-green-900">Completed</h3>
                    <p class="text-2xl font-bold text-green-600">
                        {{ $orders->where('status', 'completed')->count() }}
                    </p>
                    <p class="text-sm text-green-600">Successfully completed</p>
                </div>
            </div>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="text-3xl text-red-500">❌</div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-red-900">Cancelled</h3>
                    <p class="text-2xl font-bold text-red-600">
                        {{ $orders->where('status', 'cancelled')->count() }}
                    </p>
                    <p class="text-sm text-red-600">Cancelled orders</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">📊 Sales Reports</h1>
            <p class="text-gray-600 mt-2">Analytics and performance insights</p>
        </div>
        <a href="{{ route('admin.users') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
            ← Back to Admin Dashboard
        </a>
    </div>

    <!-- Sales Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Daily Sales -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Today's Sales</p>
                    <p class="text-3xl font-bold text-gray-900">${{ number_format($reports['daily_sales'], 2) }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ now()->format('M d, Y') }}</p>
                </div>
                <div class="text-4xl text-blue-500">📅</div>
            </div>
        </div>

        <!-- Weekly Sales -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">This Week</p>
                    <p class="text-3xl font-bold text-gray-900">${{ number_format($reports['weekly_sales'], 2) }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ now()->startOfWeek()->format('M d') }} - {{ now()->endOfWeek()->format('M d') }}</p>
                </div>
                <div class="text-4xl text-green-500">📈</div>
            </div>
        </div>

        <!-- Monthly Sales -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">This Month</p>
                    <p class="text-3xl font-bold text-gray-900">${{ number_format($reports['monthly_sales'], 2) }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ now()->format('F Y') }}</p>
                </div>
                <div class="text-4xl text-purple-500">📊</div>
            </div>
        </div>
    </div>

    <!-- System Overview -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <!-- Total Orders -->
        <div class="bg-white rounded-lg shadow-lg p-4 border-l-4 border-indigo-500">
            <div class="flex items-center">
                <div class="text-2xl mr-3">📋</div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Orders</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($reports['total_orders']) }}</p>
                </div>
            </div>
        </div>

        <!-- Completed Orders -->
        <div class="bg-white rounded-lg shadow-lg p-4 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="text-2xl mr-3">✅</div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Completed</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($reports['completed_orders']) }}</p>
                </div>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="bg-white rounded-lg shadow-lg p-4 border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="text-2xl mr-3">⏳</div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ number_format($reports['pending_orders']) }}</p>
                </div>
            </div>
        </div>

        <!-- Total Products -->
        <div class="bg-white rounded-lg shadow-lg p-4 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="text-2xl mr-3">📦</div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Products</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($reports['total_products']) }}</p>
                </div>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="bg-white rounded-lg shadow-lg p-4 border-l-4 border-red-500">
            <div class="flex items-center">
                <div class="text-2xl mr-3">⚠️</div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Low Stock</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($reports['low_stock_products']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">🏆 Top Selling Products</h3>
            <p class="text-sm text-gray-600 mt-1">Based on completed orders</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sold</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($reports['top_products'] as $index => $product)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($index === 0)
                                        <span class="text-2xl">🥇</span>
                                    @elseif($index === 1)
                                        <span class="text-2xl">🥈</span>
                                    @elseif($index === 2)
                                        <span class="text-2xl">🥉</span>
                                    @else
                                        <span class="text-lg font-bold text-gray-600">{{ $index + 1 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-mono text-gray-600">{{ $product->sku }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-bold text-green-600">{{ $product->total_sold }} units</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        @if($reports['top_products']->isNotEmpty() && $reports['top_products']->first()->total_sold > 0)
                                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ min(100, ($product->total_sold / $reports['top_products']->first()->total_sold) * 100) }}%"></div>
                                        @else
                                            <div class="bg-gray-300 h-2 rounded-full" style="width: 100%"></div>
                                        @endif
                                    </div>
                                    <span class="text-xs text-gray-600">
                                        @if($reports['top_products']->isNotEmpty() && $reports['top_products']->first()->total_sold > 0)
                                            {{ number_format(($product->total_sold / $reports['top_products']->first()->total_sold) * 100, 1) }}%
                                        @else
                                            100%
                                        @endif
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No sales data available
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Performance Insights -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales Trends -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 Sales Trends</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                    <div>
                        <p class="font-medium text-blue-900">Daily Average</p>
                        <p class="text-sm text-blue-600">Based on this month</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-blue-600">
                            ${{ number_format(now()->day > 0 ? $reports['monthly_sales'] / now()->day : 0, 2) }}
                        </p>
                        <p class="text-sm text-blue-500">per day</p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                    <div>
                        <p class="font-medium text-green-900">Weekly Average</p>
                        <p class="text-sm text-green-600">Based on this month</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-green-600">
                            ${{ number_format(now()->day >= 7 ? $reports['monthly_sales'] / (now()->day / 7) : $reports['monthly_sales'], 2) }}
                        </p>
                        <p class="text-sm text-green-500">per week</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">⚡ Quick Stats</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg">
                    <div>
                        <p class="font-medium text-yellow-900">Best Selling Product</p>
                        <p class="text-sm text-yellow-600">Top performer</p>
                    </div>
                    <div class="text-right">
                        @if($reports['top_products']->isNotEmpty())
                            <p class="text-lg font-bold text-yellow-600">
                                {{ $reports['top_products']->first()->name }}
                            </p>
                            <p class="text-sm text-yellow-500">{{ $reports['top_products']->first()->total_sold }} sold</p>
                        @else
                            <p class="text-sm text-gray-500">No data</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg">
                    <div>
                        <p class="font-medium text-purple-900">Total Products Sold</p>
                        <p class="text-sm text-purple-600">All time</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-purple-600">
                            {{ $reports['top_products']->sum('total_sold') }}
                        </p>
                        <p class="text-sm text-purple-500">units</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

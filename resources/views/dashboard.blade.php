@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Dashboard
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Welcome to Fofana! Here's what's happening with your inventory.
            </p>
        </div>

    </div>

    @if(isset($stats['error']))
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ $stats['error'] }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Orders -->
        <a href="{{ route('orders.index') }}" class="stats-card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-14 h-14 bg-gradient-to-r from-navy-600 to-navy-700 rounded-xl flex items-center justify-center group-hover:from-navy-700 group-hover:to-navy-800 transition-all duration-300 shadow-lg">
                        <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-5 flex-1">
                    <dl>
                        <dt class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Total Orders</dt>
                        <dd class="text-3xl font-bold text-navy-900" id="total-orders">{{ $stats['total_orders'] ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </a>

        <!-- Pending Orders -->
        <a href="{{ route('orders.pending') }}" class="stats-card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-14 h-14 bg-gradient-to-r from-mustard-500 to-mustard-600 rounded-xl flex items-center justify-center group-hover:from-mustard-600 group-hover:to-mustard-700 transition-all duration-300 shadow-lg">
                        <svg class="h-7 w-7 text-navy-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-5 flex-1">
                    <dl>
                        <dt class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Processing Orders</dt>
                        <dd class="text-3xl font-bold text-navy-900">{{ $stats['pending_orders'] ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </a>

        <!-- Total Products -->
        <a href="{{ route('products.index') }}" class="stats-card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-14 h-14 bg-gradient-to-r from-navy-500 to-navy-600 rounded-xl flex items-center justify-center group-hover:from-navy-600 group-hover:to-navy-700 transition-all duration-300 shadow-lg">
                        <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
                <div class="ml-5 flex-1">
                    <dl>
                        <dt class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Total Products</dt>
                        <dd class="text-3xl font-bold text-navy-900">{{ $stats['total_products'] ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </a>

        <!-- Low Stock Items -->
        <a href="{{ route('products.index', ['filter' => 'low_stock']) }}" class="stats-card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-14 h-14 bg-gradient-to-r from-red-500 to-red-600 rounded-xl flex items-center justify-center group-hover:from-red-600 group-hover:to-red-700 transition-all duration-300 shadow-lg">
                        <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-5 flex-1">
                    <dl>
                        <dt class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Low Stock Items</dt>
                        <dd class="text-3xl font-bold text-navy-900">{{ $stats['low_stock_products'] ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </a>
    </div>

    <!-- Additional Stats Row -->
    @if(isset($stats['today_orders']) || isset($stats['revenue_today']))
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @if(isset($stats['today_orders']))
        <div class="stats-card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-14 h-14 bg-gradient-to-r from-mustard-400 to-mustard-500 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="h-7 w-7 text-navy-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-5 flex-1">
                    <dl>
                        <dt class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Today's Orders</dt>
                        <dd class="text-3xl font-bold text-navy-900">{{ $stats['today_orders'] }}</dd>
                        <div class="mt-2 flex items-center text-sm text-gray-600">
                            <svg class="w-4 h-4 mr-1 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                            <span class="font-medium">Orders placed today</span>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
        @endif


    </div>
    @endif


</div>
@endsection
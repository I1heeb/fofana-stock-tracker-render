<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $direction ?? 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Stock Tracker') }} - @yield('title', 'Dashboard')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-inter antialiased bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        <div id="sidebar" class="hidden md:flex md:w-64 md:flex-col transition-all duration-300 ease-in-out">
            <div class="flex flex-col flex-grow pt-5 overflow-y-auto bg-navy-900 shadow-xl">
                <!-- Logo and Toggle Button -->
                <div class="flex items-center justify-between flex-shrink-0 px-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gradient-to-r from-mustard-400 to-mustard-500 rounded-lg flex items-center justify-center shadow-lg">
                                <svg class="w-5 h-5 text-navy-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3" id="sidebar-title">
                            <h1 class="text-xl font-bold text-white">Fofana Stock</h1>
                        </div>
                    </div>
                    <!-- Toggle Button -->
                    <button id="sidebar-toggle" class="p-1 rounded-md text-gray-300 hover:text-white hover:bg-navy-800 focus:outline-none focus:ring-2 focus:ring-mustard-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                        </svg>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="mt-8 flex-1 px-2 space-y-1">
                    <a href="{{ route('dashboard') }}" class="group flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-mustard-400 text-navy-900 shadow-lg' : 'text-gray-300 hover:bg-navy-800 hover:text-white' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('dashboard') ? 'text-navy-900' : 'text-gray-400 group-hover:text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"/>
                        </svg>
                        Dashboard
                    </a>

                    <a href="{{ route('products.index') }}" class="group flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('products.*') ? 'bg-mustard-400 text-navy-900 shadow-lg' : 'text-gray-300 hover:bg-navy-800 hover:text-white' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('products.*') ? 'text-navy-900' : 'text-gray-400 group-hover:text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Products
                    </a>

                    <a href="{{ route('orders.index') }}" class="group flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('orders.*') ? 'bg-mustard-400 text-navy-900 shadow-lg' : 'text-gray-300 hover:bg-navy-800 hover:text-white' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('orders.*') ? 'text-navy-900' : 'text-gray-400 group-hover:text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Orders
                    </a>

                    <a href="{{ route('products.index', ['filter' => 'low_stock']) }}" class="group flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200 text-gray-300 hover:bg-navy-800 hover:text-white">
                        <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        Low Stock Items
                    </a>

                    <!-- Admin Dashboard - Visible for admin users only -->
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="group flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.*') ? 'bg-mustard-400 text-navy-900 shadow-lg' : 'text-gray-300 hover:bg-navy-800 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.*') ? 'text-navy-900' : 'text-gray-400 group-hover:text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            🛡️ Admin Dashboard
                        </a>
                    @endif
                </nav>



                <!-- User Profile Section -->
                <div class="flex-shrink-0 border-t border-navy-800 p-4 space-y-3">
                    <div class="flex items-center w-full">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-r from-mustard-400 to-mustard-500 rounded-full flex items-center justify-center shadow-lg">
                                <span class="text-sm font-bold text-navy-900">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            </div>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-300">{{ auth()->user()->getRoleDisplayName() }}</p>
                        </div>
                    </div>



                    <!-- Logout Button -->
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="w-full group flex items-center px-3 py-2 text-sm font-medium text-gray-300 hover:text-white hover:bg-red-600 rounded-lg transition-all duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Mobile menu button -->
        <div class="md:hidden">
            <div class="fixed inset-0 flex z-40" id="mobile-menu" style="display: none;">
                <div class="fixed inset-0 bg-gray-600 bg-opacity-75"></div>
                <div class="relative flex-1 flex flex-col max-w-xs w-full bg-white">
                    <div class="absolute top-0 right-0 -mr-12 pt-2">
                        <button type="button" class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white" onclick="document.getElementById('mobile-menu').style.display='none'">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <!-- Mobile navigation content would go here -->
                </div>
            </div>
        </div>

        <!-- Collapsed Sidebar Button -->
        <div id="collapsed-sidebar" class="hidden fixed left-0 top-0 z-50 bg-white shadow-lg rounded-r-lg">
            <button id="expand-sidebar" class="p-3 text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        <!-- Main content area -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <!-- Top header for mobile -->
            <div class="md:hidden">
                <div class="relative z-10 flex-shrink-0 flex h-16 bg-white shadow">
                    <button type="button" class="px-4 border-r border-gray-200 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 md:hidden" onclick="document.getElementById('mobile-menu').style.display='flex'">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                    </button>
                    <div class="flex-1 px-4 flex justify-between">
                        <div class="flex-1 flex">
                            <h1 class="text-xl font-semibold text-gray-900 flex items-center">Stock Tracker</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <main class="flex-1 relative overflow-y-auto focus:outline-none">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const collapsedSidebar = document.getElementById('collapsed-sidebar');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const expandSidebar = document.getElementById('expand-sidebar');
            const sidebarTitle = document.getElementById('sidebar-title');

            // Toggle sidebar function
            function toggleSidebar() {
                if (sidebar.classList.contains('md:w-64')) {
                    // Collapse sidebar
                    sidebar.classList.remove('md:w-64');
                    sidebar.classList.add('md:w-0', 'md:overflow-hidden');
                    collapsedSidebar.classList.remove('hidden');

                    // Hide sidebar content
                    const sidebarContent = sidebar.querySelector('.flex-col');
                    if (sidebarContent) {
                        sidebarContent.style.opacity = '0';
                        setTimeout(() => {
                            sidebarContent.style.display = 'none';
                        }, 150);
                    }
                } else {
                    // Expand sidebar
                    sidebar.classList.remove('md:w-0', 'md:overflow-hidden');
                    sidebar.classList.add('md:w-64');
                    collapsedSidebar.classList.add('hidden');

                    // Show sidebar content
                    const sidebarContent = sidebar.querySelector('.flex-col');
                    if (sidebarContent) {
                        sidebarContent.style.display = 'flex';
                        setTimeout(() => {
                            sidebarContent.style.opacity = '1';
                        }, 50);
                    }
                }
            }

            // Event listeners
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', toggleSidebar);
            }

            if (expandSidebar) {
                expandSidebar.addEventListener('click', toggleSidebar);
            }

            // Initialize sidebar content opacity
            const sidebarContent = sidebar.querySelector('.flex-col');
            if (sidebarContent) {
                sidebarContent.style.transition = 'opacity 0.3s ease-in-out';
                sidebarContent.style.opacity = '1';
            }
        });
    </script>
</body>
</html>

    <!-- Bootstrap JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
</body>
</html>


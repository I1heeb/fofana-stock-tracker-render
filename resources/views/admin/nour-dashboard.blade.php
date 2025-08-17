@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <!-- Header personnalisé pour Nour -->
    <div class="bg-white shadow-lg border-b-4 border-indigo-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center">
                    <div class="bg-indigo-500 text-white rounded-full w-12 h-12 flex items-center justify-center text-xl font-bold">
                        N
                    </div>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-gray-900">Bienvenue, Nour Admin</h1>
                        <p class="text-sm text-gray-600">Tableau de bord administrateur - Fofana Stock Tracker</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Connecté en tant que</p>
                        <p class="font-semibold text-indigo-600">{{ auth()->user()->email }}</p>
                    </div>
                    <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                        🛡️ Super Admin
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Statistiques rapides -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="text-3xl mr-4">👥</div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Utilisateurs</p>
                        <p class="text-2xl font-bold text-gray-900">{{ \App\Models\User::count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="text-3xl mr-4">📦</div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Produits</p>
                        <p class="text-2xl font-bold text-gray-900">{{ \App\Models\Product::count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="text-3xl mr-4">📋</div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Commandes</p>
                        <p class="text-2xl font-bold text-gray-900">{{ \App\Models\Order::count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-red-500">
                <div class="flex items-center">
                    <div class="text-3xl mr-4">⚠️</div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Stock Faible</p>
                        <p class="text-2xl font-bold text-gray-900">{{ \App\Models\Product::whereRaw('stock_quantity <= low_stock_threshold')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides pour Nour -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">🚀 Actions Rapides - Administration</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <a href="{{ route('admin.users') }}" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                    <div class="text-2xl mb-2">👥</div>
                    <span class="text-sm font-medium text-blue-800">Utilisateurs</span>
                </a>

                <a href="{{ route('products.index') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                    <div class="text-2xl mb-2">📦</div>
                    <span class="text-sm font-medium text-green-800">Produits</span>
                </a>

                <a href="{{ route('orders.index') }}" class="flex flex-col items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                    <div class="text-2xl mb-2">📋</div>
                    <span class="text-sm font-medium text-yellow-800">Commandes</span>
                </a>

                <a href="{{ route('admin.reports') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                    <div class="text-2xl mb-2">📊</div>
                    <span class="text-sm font-medium text-purple-800">Rapports</span>
                </a>

                <a href="{{ route('products.low-stock') }}" class="flex flex-col items-center p-4 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                    <div class="text-2xl mb-2">⚠️</div>
                    <span class="text-sm font-medium text-red-800">Stock Faible</span>
                </a>

                <a href="{{ route('admin.users.create') }}" class="flex flex-col items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                    <div class="text-2xl mb-2">➕</div>
                    <span class="text-sm font-medium text-indigo-800">Nouvel User</span>
                </a>
            </div>
        </div>

        <!-- Informations système -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Activité récente -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">📈 Activité Récente</h3>
                <div class="space-y-3">
                    @php
                        $recentOrders = \App\Models\Order::latest()->take(5)->get();
                    @endphp
                    @forelse($recentOrders as $order)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                                <div>
                                    <p class="text-sm font-medium">Commande #{{ $order->order_number }}</p>
                                    <p class="text-xs text-gray-600">{{ $order->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-green-600">${{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">Aucune commande récente</p>
                    @endforelse
                </div>
            </div>

            <!-- Utilisateurs récents -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">👤 Utilisateurs Récents</h3>
                <div class="space-y-3">
                    @php
                        $recentUsers = \App\Models\User::latest()->take(5)->get();
                    @endphp
                    @foreach($recentUsers as $user)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-indigo-500 text-white rounded-full flex items-center justify-center text-sm font-bold mr-3">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-600">{{ $user->email }}</p>
                                </div>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full 
                                @if($user->role === 'admin') bg-red-100 text-red-800
                                @elseif($user->role === 'packaging_agent') bg-yellow-100 text-yellow-800
                                @else bg-blue-100 text-blue-800 @endif">
                                {{ ucfirst($user->role) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Alertes et notifications -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-bold text-gray-900 mb-4">🔔 Alertes Système</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @php
                    $lowStockCount = \App\Models\Product::whereRaw('stock_quantity <= low_stock_threshold')->count();
                    $pendingOrders = \App\Models\Order::where('status', 'in_progress')->count();
                @endphp

                @if($lowStockCount > 0)
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="text-red-500 mr-3">⚠️</div>
                            <div>
                                <p class="font-medium text-red-800">Stock Faible</p>
                                <p class="text-sm text-red-600">{{ $lowStockCount }} produit(s) nécessitent un réapprovisionnement</p>
                            </div>
                        </div>
                        <a href="{{ route('products.low-stock') }}" class="text-red-600 hover:text-red-800 text-sm font-medium mt-2 inline-block">
                            Voir les détails →
                        </a>
                    </div>
                @endif

                @if($pendingOrders > 0)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="text-yellow-500 mr-3">⏳</div>
                            <div>
                                <p class="font-medium text-yellow-800">Commandes en Attente</p>
                                <p class="text-sm text-yellow-600">{{ $pendingOrders }} commande(s) à traiter</p>
                            </div>
                        </div>
                        <a href="{{ route('orders.index') }}" class="text-yellow-600 hover:text-yellow-800 text-sm font-medium mt-2 inline-block">
                            Traiter les commandes →
                        </a>
                    </div>
                @endif

                @if($lowStockCount === 0 && $pendingOrders === 0)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 md:col-span-2">
                        <div class="flex items-center justify-center">
                            <div class="text-green-500 mr-3">✅</div>
                            <p class="font-medium text-green-800">Tout va bien ! Aucune alerte système.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Informations de session -->
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-6">
            <h3 class="text-lg font-bold text-indigo-900 mb-4">ℹ️ Informations de Session</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="font-medium text-indigo-800">Utilisateur</p>
                    <p class="text-indigo-600">{{ auth()->user()->name }}</p>
                    <p class="text-indigo-600">{{ auth()->user()->email }}</p>
                </div>
                <div>
                    <p class="font-medium text-indigo-800">Rôle & Permissions</p>
                    <p class="text-indigo-600">{{ ucfirst(auth()->user()->role) }}</p>
                    <p class="text-indigo-600">Accès complet système</p>
                </div>
                <div>
                    <p class="font-medium text-indigo-800">Session</p>
                    <p class="text-indigo-600">Connecté depuis {{ auth()->user()->created_at->diffForHumans() }}</p>
                    <p class="text-indigo-600">ID: {{ auth()->user()->id }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Admin Test Header -->
    <div class="card">
        <div class="card-header">
            <h1 class="heading-1">🔐 Test Connexion Admin</h1>
        </div>
        
        <div class="space-y-4">
            <div class="bg-green-100 border-l-4 border-green-500 p-4 rounded">
                <h3 class="heading-3 text-green-800">✅ Utilisateur Admin Créé avec Succès</h3>
                <p class="text-green-700">L'utilisateur admin a été créé et configuré correctement.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Informations de connexion -->
                <div class="bg-navy-50 p-6 rounded-lg border border-navy-200">
                    <h3 class="heading-3 text-navy-900 mb-4">📋 Informations de Connexion</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="font-semibold text-navy-700">Email:</span>
                            <code class="bg-navy-100 px-2 py-1 rounded text-navy-900">aaaa@dev.com</code>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-navy-700">Mot de passe:</span>
                            <code class="bg-navy-100 px-2 py-1 rounded text-navy-900">nouramara</code>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-navy-700">Nom:</span>
                            <span class="text-navy-900">aaaa</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-navy-700">Rôle:</span>
                            <span class="bg-mustard-500 text-navy-900 px-2 py-1 rounded font-semibold">Admin</span>
                        </div>
                    </div>
                </div>

                <!-- Utilisateur actuel -->
                <div class="bg-mustard-50 p-6 rounded-lg border border-mustard-200">
                    <h3 class="heading-3 text-mustard-800 mb-4">👤 Utilisateur Actuel</h3>
                    @auth
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="font-semibold text-mustard-700">Nom:</span>
                                <span class="text-mustard-900">{{ auth()->user()->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-semibold text-mustard-700">Email:</span>
                                <span class="text-mustard-900">{{ auth()->user()->email }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-semibold text-mustard-700">Rôle:</span>
                                <span class="bg-navy-500 text-white px-2 py-1 rounded font-semibold">{{ auth()->user()->role }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-semibold text-mustard-700">Est Admin:</span>
                                <span class="text-mustard-900">{{ auth()->user()->isAdmin() ? '✅ Oui' : '❌ Non' }}</span>
                            </div>
                        </div>
                    @else
                        <p class="text-mustard-700">Aucun utilisateur connecté</p>
                    @endauth
                </div>
            </div>

            <!-- Actions disponibles -->
            <div class="card">
                <div class="card-header">
                    <h3 class="heading-3">🚀 Actions Disponibles</h3>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.users.index') }}" class="btn-primary text-center">
                                👥 Gestion des Utilisateurs
                            </a>
                            <a href="{{ route('dashboard') }}" class="btn-secondary text-center">
                                📊 Dashboard Admin
                            </a>
                            <a href="{{ route('test.design') }}" class="btn-outline text-center">
                                🎨 Test Design
                            </a>
                        @else
                            <div class="bg-red-100 border border-red-300 p-4 rounded-lg text-center">
                                <p class="text-red-700">❌ Accès admin requis</p>
                            </div>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn-primary text-center">
                            🔑 Se Connecter
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Instructions -->
            <div class="bg-blue-50 border border-blue-200 p-6 rounded-lg">
                <h3 class="heading-3 text-blue-800 mb-4">📝 Instructions</h3>
                <ol class="list-decimal list-inside space-y-2 text-blue-700">
                    <li>Si vous n'êtes pas connecté, cliquez sur "Se Connecter"</li>
                    <li>Utilisez les identifiants : <strong>aaaa@dev.com</strong> / <strong>nouramara</strong></li>
                    <li>Une fois connecté, vous aurez accès à toutes les fonctionnalités admin</li>
                    <li>Testez la sidebar rétractable et le nouveau design</li>
                    <li>Vérifiez que Dashboard et Logout sont toujours visibles</li>
                </ol>
            </div>

            <!-- Statut de la base de données -->
            <div class="bg-gray-50 border border-gray-200 p-6 rounded-lg">
                <h3 class="heading-3 text-gray-800 mb-4">💾 Statut Base de Données</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                    <div class="bg-white p-4 rounded border">
                        <div class="text-2xl font-bold text-navy-900">{{ \App\Models\User::count() }}</div>
                        <div class="text-sm text-gray-600">Utilisateurs Total</div>
                    </div>
                    <div class="bg-white p-4 rounded border">
                        <div class="text-2xl font-bold text-mustard-600">{{ \App\Models\User::where('role', 'admin')->count() }}</div>
                        <div class="text-sm text-gray-600">Administrateurs</div>
                    </div>
                    <div class="bg-white p-4 rounded border">
                        <div class="text-2xl font-bold text-green-600">✅</div>
                        <div class="text-sm text-gray-600">Système Opérationnel</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

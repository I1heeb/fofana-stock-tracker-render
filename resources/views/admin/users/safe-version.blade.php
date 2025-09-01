@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">User Management - SAFE VERSION</h1>
        
        <div class="mb-4 p-4 bg-gray-50 rounded-lg">
            <p class="font-semibold">
                Current User: {{ auth()->user()->name }} ({{ auth()->user()->email }})
                @if(auth()->user()->email === 'iheb@admin.com')
                    <span class="ml-2 px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                        🔥 SUPER ADMIN
                    </span>
                @elseif(auth()->user()->role === 'admin')
                    <span class="ml-2 px-3 py-1 text-xs font-bold rounded-full bg-purple-100 text-purple-800">
                        👤 ADMIN
                    </span>
                @endif
            </p>
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

        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2 border text-left">User</th>
                        <th class="px-4 py-2 border text-left">Email</th>
                        <th class="px-4 py-2 border text-left">Role & Status</th>
                        <th class="px-4 py-2 border text-left">Created</th>
                        <th class="px-4 py-2 border text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr class="{{ $user->email === 'iheb@admin.com' ? 'bg-red-50' : ($user->role === 'admin' ? 'bg-purple-50' : '') }}">
                        <td class="px-4 py-2 border">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full {{ $user->email === 'iheb@admin.com' ? 'bg-red-500' : ($user->role === 'admin' ? 'bg-purple-500' : 'bg-blue-500') }} flex items-center justify-center text-white font-bold text-sm mr-3">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium">
                                        {{ $user->name }}
                                        @if($user->email === 'iheb@admin.com')
                                            <span class="ml-1 text-red-500">🔥</span>
                                        @elseif($user->role === 'admin')
                                            <span class="ml-1 text-purple-500">👤</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-2 border">{{ $user->email }}</td>
                        <td class="px-4 py-2 border">
                            @if($user->email === 'iheb@admin.com')
                                <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                                    🔥 SUPER ADMIN
                                </span>
                            @elseif($user->role === 'admin')
                                <span class="px-2 py-1 text-xs font-bold rounded-full bg-purple-100 text-purple-800">
                                    👤 ADMIN
                                </span>
                            @elseif($user->role === 'packaging_agent')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    📦 PACKAGING AGENT
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    🛒 SERVICE CLIENT
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-2 border">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-2 border">
                            <div class="flex flex-col space-y-1">
                                {{-- SUPER ADMIN ACTIONS (iheb@admin.com only) --}}
                                @if(auth()->user()->email === 'iheb@admin.com' && $user->id !== auth()->id())
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:underline text-xs">
                                        ✏️ Edit
                                    </a>
                                    
                                    @if($user->email !== 'iheb@admin.com')
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline text-xs" onclick="return confirm('Delete {{ $user->name }}?')">
                                                🗑️ Delete
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($user->role === 'admin')
                                        @if($user->is_super_admin)
                                            <form action="{{ route('admin.users.remove-super-admin', $user) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-orange-600 hover:underline text-xs" onclick="return confirm('Remove super admin from {{ $user->name }}?')">
                                                    👑➖ Remove Super Admin
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.users.make-super-admin', $user) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-purple-600 hover:underline text-xs" onclick="return confirm('Make {{ $user->name }} super admin?')">
                                                    👑➕ Make Super Admin
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                
                                {{-- REGULAR ADMIN ACTIONS --}}
                                @elseif(auth()->user()->role === 'admin' && $user->role !== 'admin' && $user->id !== auth()->id())
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:underline text-xs">
                                        ✏️ Edit
                                    </a>
                                    
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-xs" onclick="return confirm('Delete {{ $user->name }}?')">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                
                                {{-- PROTECTED USER --}}
                                @else
                                    @if($user->id !== auth()->id())
                                        <span class="text-gray-400 text-xs">🔒 Protected</span>
                                    @else
                                        <span class="text-gray-600 text-xs">👤 You</span>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection

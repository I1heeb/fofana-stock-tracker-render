@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">User Management</h1>

        <div class="mb-4 p-4 bg-gray-50 rounded-lg">
            <p class="font-semibold">
                Current User: {{ auth()->user()->name }} ({{ auth()->user()->email }})
                @if(auth()->user()->isSuperAdmin())
                    <span class="ml-2 px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                        🔥 SUPER ADMIN - Full System Access
                    </span>
                @elseif(auth()->user()->isAdmin())
                    <span class="ml-2 px-3 py-1 text-xs font-bold rounded-full bg-purple-100 text-purple-800">
                        👤 ADMIN - Limited Access
                    </span>
                @endif
            </p>

            @if(auth()->user()->isSuperAdmin())
                <p class="text-sm text-gray-600 mt-2">
                    ✅ You can edit/delete all users and manage admin roles
                </p>
            @elseif(auth()->user()->isAdmin())
                <p class="text-sm text-gray-600 mt-2">
                    ✅ You can edit/delete packaging agents and service clients only
                </p>
            @endif
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
                    <tr class="{{ $user->isSuperAdmin() ? 'bg-red-50' : ($user->isAdmin() ? 'bg-purple-50' : '') }}">
                        <td class="px-4 py-2 border">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full {{ $user->isSuperAdmin() ? 'bg-red-500' : ($user->isAdmin() ? 'bg-purple-500' : 'bg-blue-500') }} flex items-center justify-center text-white font-bold text-sm mr-3">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium">
                                        {{ $user->name }}
                                        @if($user->isSuperAdmin())
                                            <span class="ml-1 text-red-500">🔥</span>
                                        @elseif($user->isAdmin())
                                            <span class="ml-1 text-purple-500">👤</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-2 border">{{ $user->email }}</td>
                        <td class="px-4 py-2 border">
                            @if($user->isSuperAdmin())
                                <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                                    🔥 SUPER ADMIN
                                </span>
                            @elseif($user->isAdmin())
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
                                @if(auth()->user()->canManageUser($user))
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:underline text-xs">
                                        ✏️ Edit
                                    </a>
                                @endif

                                @if(auth()->user()->canDeleteUser($user))
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-xs" onclick="return confirm('Delete {{ $user->name }}?')">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                @endif

                                @if(auth()->user()->isSuperAdmin() && $user->isAdmin() && $user->id !== auth()->id())
                                    @if($user->isSuperAdmin())
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

                                @if(!auth()->user()->canManageUser($user) && $user->id !== auth()->id())
                                    <span class="text-gray-400 text-xs">🔒 Protected</span>
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

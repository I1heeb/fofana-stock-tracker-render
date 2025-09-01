@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">User Management - SUPER ADMIN VERSION</h1>
            @if(auth()->user()->canCreateCustomAccounts())
                <a href="{{ route('admin.users.create-advanced') }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    🔥 Create Advanced User
                </a>
            @endif
        </div>
        
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

                                    {{-- PASSWORD MANAGEMENT --}}
                                    <button onclick="viewPassword({{ $user->id }}, '{{ $user->name }}')" class="text-green-600 hover:underline text-xs">
                                        👁️ View Password
                                    </button>

                                    <button onclick="changePassword({{ $user->id }}, '{{ $user->name }}')" class="text-purple-600 hover:underline text-xs">
                                        🔑 Change Password
                                    </button>

                                    {{-- DELETE (with super admin protection) --}}
                                    @if($user->canBeDeletedBy(auth()->user()))
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline text-xs" onclick="return confirm('Delete {{ $user->name }}?')">
                                                🗑️ Delete
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-400 text-xs">🔒 Protected</span>
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

{{-- PASSWORD MANAGEMENT MODALS --}}
@if(auth()->user()->canViewPasswords())
<!-- View Password Modal -->
<div id="passwordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">👁️ View Password</h3>
            <div id="passwordContent" class="mb-4"></div>
            <div class="flex justify-end">
                <button onclick="closePasswordModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div id="changePasswordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🔑 Change Password</h3>
            <form id="changePasswordForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">New Password</label>
                    <input type="password" name="new_password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input type="password" name="new_password_confirmation" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeChangePasswordModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Cancel
                    </button>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewPassword(userId, userName) {
    fetch(`/admin/users/${userId}/view-password`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('passwordContent').innerHTML = `
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p><strong>User:</strong> ${data.user}</p>
                    <p><strong>Email:</strong> ${data.email}</p>
                    <p><strong>Password:</strong> <code class="bg-yellow-100 px-2 py-1 rounded">${data.password}</code></p>
                </div>
            `;
            document.getElementById('passwordModal').classList.remove('hidden');
        })
        .catch(error => {
            alert('Error viewing password: ' + error.message);
        });
}

function changePassword(userId, userName) {
    document.getElementById('changePasswordForm').action = `/admin/users/${userId}/change-password`;
    document.getElementById('changePasswordModal').classList.remove('hidden');
}

function closePasswordModal() {
    document.getElementById('passwordModal').classList.add('hidden');
}

function closeChangePasswordModal() {
    document.getElementById('changePasswordModal').classList.add('hidden');
}
</script>
@endif

@endsection

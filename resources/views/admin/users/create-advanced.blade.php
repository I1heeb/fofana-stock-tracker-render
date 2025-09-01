@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">🔥 Super Admin - Create Advanced User</h1>
            <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                ← Back to Users
            </a>
        </div>

        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-800 font-semibold">🔥 Super Admin Only Feature</p>
            <p class="text-red-700 text-sm">Create users with custom permissions and roles. Only super admins can access this feature.</p>
        </div>

        <form action="{{ route('admin.users.store-advanced') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Basic Information --}}
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">👤 Basic Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" id="password" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            {{-- Role Selection --}}
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🎭 Role & Status</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">User Role</label>
                        <select name="role" id="role" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select Role</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>👤 Admin</option>
                            <option value="packaging_agent" {{ old('role') == 'packaging_agent' ? 'selected' : '' }}>📦 Packaging Agent</option>
                            <option value="service_client" {{ old('role') == 'service_client' ? 'selected' : '' }}>🛒 Service Client</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="flex items-center mt-6">
                            <input type="checkbox" name="is_super_admin" value="1" {{ old('is_super_admin') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">🔥 Make Super Admin</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1">Super admins have unlimited access to everything</p>
                    </div>
                </div>
            </div>

            {{-- Custom Permissions --}}
            <div class="bg-purple-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🔐 Custom Permissions</h3>
                <p class="text-sm text-gray-600 mb-4">Select specific permissions for this user (optional - defaults will be applied based on role)</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($allPermissions as $permission => $label)
                        <label class="flex items-center p-2 bg-white rounded border hover:bg-gray-50">
                            <input type="checkbox" name="permissions[]" value="{{ $permission }}"
                                   {{ in_array($permission, old('permissions', [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.users.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    🔥 Create Advanced User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-select permissions based on role
document.getElementById('role').addEventListener('change', function() {
    const role = this.value;
    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
    
    // Clear all checkboxes first
    checkboxes.forEach(cb => cb.checked = false);
    
    // Auto-select based on role
    if (role === 'admin') {
        ['manage_users', 'manage_products', 'manage_orders', 'view_reports'].forEach(perm => {
            const checkbox = document.querySelector(`input[value="${perm}"]`);
            if (checkbox) checkbox.checked = true;
        });
    } else if (role === 'packaging_agent') {
        ['manage_orders', 'manage_products'].forEach(perm => {
            const checkbox = document.querySelector(`input[value="${perm}"]`);
            if (checkbox) checkbox.checked = true;
        });
    }
});

// Super admin checkbox behavior
document.querySelector('input[name="is_super_admin"]').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
    if (this.checked) {
        // Check all permissions for super admin
        checkboxes.forEach(cb => cb.checked = true);
    }
});
</script>
@endsection

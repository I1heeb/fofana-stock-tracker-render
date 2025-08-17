<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Activity Logs</h2>
                        <div class="flex space-x-2">
                            <button onclick="exportLogs('csv')" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm">
                                Download CSV
                            </button>
                            <button onclick="exportLogs('pdf')" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm">
                                Print PDF
                            </button>
                        </div>
                    </div>

                    <!-- Advanced Filters -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <form method="GET" action="{{ route('reports.logs') }}" id="filterForm">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <!-- Date Range Picker -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">From Date</label>
                                    <input type="date" name="from" value="{{ request('from') }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">To Date</label>
                                    <input type="date" name="to" value="{{ request('to') }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>

                                <!-- Type Dropdown -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Type</label>
                                    <select name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">All Types</option>
                                        <option value="info" {{ request('type') == 'info' ? 'selected' : '' }}>Info</option>
                                        <option value="warning" {{ request('type') == 'warning' ? 'selected' : '' }}>Warning</option>
                                        <option value="error" {{ request('type') == 'error' ? 'selected' : '' }}>Error</option>
                                    </select>
                                </div>

                                <!-- User Dropdown -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">User</label>
                                    <select name="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">All Users</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Live Search -->
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700">Search</label>
                                <input type="text" name="search" value="{{ request('search') }}" 
                                       placeholder="Search in actions, descriptions..." 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                       id="liveSearch">
                            </div>

                            <div class="mt-4 flex space-x-2">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm">
                                    Apply Filters
                                </button>
                                <a href="{{ route('reports.logs') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm">
                                    Clear
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Logs Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="logsTableBody">
                                @foreach($logs as $log)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $log->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $log->user->name ?? 'System' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $log->action }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            {{ $log->type == 'error' ? 'bg-red-100 text-red-800' : 
                                               ($log->type == 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                            {{ ucfirst($log->type ?? 'info') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $log->description ?? $log->message }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Live search functionality
        let searchTimeout;
        document.getElementById('liveSearch').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('filterForm').submit();
            }, 500);
        });

        // Export functions
        function exportLogs(format) {
            const params = new URLSearchParams(window.location.search);
            params.set('format', format);
            window.location.href = '{{ route("reports.logs.export") }}?' + params.toString();
        }
    </script>
    @endpush
</x-app-layout>
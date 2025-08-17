<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Offline') }} - {{ config('app.name') }}</title>
    <link rel="icon" href="/favicon.ico">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md mx-auto text-center p-6">
        <div class="mb-8">
            <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5z"/>
            </svg>
        </div>
        
        <h1 class="text-2xl font-bold text-gray-900 mb-4">
            {{ __('You are offline') }}
        </h1>
        
        <p class="text-gray-600 mb-8">
            {{ __('Please check your internet connection and try again.') }}
        </p>
        
        <div class="space-y-4">
            <button onclick="window.location.reload()" 
                    class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                {{ __('Try Again') }}
            </button>
            
            <button onclick="goToCache()" 
                    class="w-full bg-gray-200 text-gray-800 py-3 px-4 rounded-lg font-medium hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                {{ __('View Cached Data') }}
            </button>
        </div>
        
        <div class="mt-8 p-4 bg-blue-50 rounded-lg">
            <h3 class="font-medium text-blue-900 mb-2">{{ __('Offline Features Available') }}</h3>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>• {{ __('View cached orders') }}</li>
                <li>• {{ __('Browse product catalog') }}</li>
                <li>• {{ __('Create orders (will sync when online)') }}</li>
            </ul>
        </div>
    </div>

    <script>
        function goToCache() {
            // Try to navigate to cached dashboard
            window.location.href = '/dashboard';
        }
        
        // Auto-retry when back online
        window.addEventListener('online', () => {
            window.location.reload();
        });
        
        // Show online/offline status
        function updateOnlineStatus() {
            const status = navigator.onLine ? 'online' : 'offline';
            document.body.className = `bg-gray-50 min-h-screen flex items-center justify-center ${status}`;
        }
        
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
    </script>
</body>
</html>
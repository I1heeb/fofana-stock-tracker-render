<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Login - Fofana Stock Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <!-- Emergency Login Card -->
        <div class="bg-white rounded-xl shadow-2xl p-8 border-t-4 border-red-500">
            <div class="text-center mb-8">
                <div class="bg-red-100 text-red-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Emergency Login</h1>
                <p class="text-gray-600">CSRF Token Issues - Direct Login</p>
            </div>

            <!-- CSRF Status -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="text-yellow-600 mr-3">⚠️</div>
                    <div>
                        <p class="text-sm font-medium text-yellow-800">CSRF Protection Bypassed</p>
                        <p class="text-xs text-yellow-600">This is a temporary emergency login</p>
                    </div>
                </div>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('emergency.login.submit') }}" id="emergencyLoginForm">
                <!-- Fresh CSRF Token -->
                <input type="hidden" name="_token" value="{{ csrf_token() }}" id="csrfToken">
                
                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="nour@admin.com"
                           required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="nouramara"
                           required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    🔐 Emergency Login
                </button>
            </form>

            <!-- Admin Credentials -->
            <div class="mt-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h3 class="font-semibold text-blue-800 mb-2">👑 Admin Nour Credentials</h3>
                <div class="text-sm text-blue-700 space-y-1">
                    <p><strong>Email:</strong> <code class="bg-white px-2 py-1 rounded">nour@admin.com</code></p>
                    <p><strong>Password:</strong> <code class="bg-white px-2 py-1 rounded">nouramara</code></p>
                    <p><strong>Role:</strong> <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Super Admin</span></p>
                </div>
            </div>

            <!-- Token Refresh -->
            <div class="mt-6 text-center">
                <button onclick="refreshToken()" 
                        class="text-sm text-blue-600 hover:text-blue-800 underline">
                    🔄 Refresh CSRF Token
                </button>
            </div>

            <!-- Links -->
            <div class="mt-8 text-center space-y-2">
                <a href="{{ route('login') }}" class="block text-sm text-gray-600 hover:text-gray-800">
                    ← Back to Normal Login
                </a>
                <a href="{{ route('register') }}" class="block text-sm text-blue-600 hover:text-blue-800">
                    Create New Account
                </a>
            </div>
        </div>

        <!-- Debug Info -->
        <div class="mt-6 bg-gray-800 text-white rounded-lg p-4 text-xs">
            <h4 class="font-semibold mb-2">🔧 Debug Information</h4>
            <div class="space-y-1">
                <p><strong>Current Token:</strong> <span class="font-mono">{{ csrf_token() }}</span></p>
                <p><strong>Session ID:</strong> <span class="font-mono">{{ session()->getId() }}</span></p>
                <p><strong>Time:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
                <p><strong>Environment:</strong> {{ app()->environment() }}</p>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh token every 30 seconds
        setInterval(refreshToken, 30000);

        function refreshToken() {
            fetch('/csrf-token')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('csrfToken').value = data.csrf_token;
                    console.log('🔄 CSRF token refreshed:', data.csrf_token);
                })
                .catch(error => {
                    console.error('❌ Token refresh failed:', error);
                });
        }

        // Form submission with fresh token
        document.getElementById('emergencyLoginForm').addEventListener('submit', function(e) {
            // Get fresh token before submit
            refreshToken();
            
            // Small delay to ensure token is updated
            setTimeout(() => {
                console.log('🚀 Submitting form with token:', document.getElementById('csrfToken').value);
            }, 100);
        });

        // Auto-focus password field if email is pre-filled
        if (document.getElementById('email').value) {
            document.getElementById('password').focus();
        }
    </script>
</body>
</html>

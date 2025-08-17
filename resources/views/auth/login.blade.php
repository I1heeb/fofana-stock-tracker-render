<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Messages d'erreur CSRF -->
    @if(session('warning'))
        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex items-center">
                <div class="text-yellow-600 mr-2">⚠️</div>
                <div class="text-yellow-800">{{ session('warning') }}</div>
            </div>
        </div>
    @endif

    @if($errors->has('csrf'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center">
                <div class="text-red-600 mr-2">🔒</div>
                <div class="text-red-800">{{ $errors->first('csrf') }}</div>
            </div>
            <div class="mt-2 text-sm text-red-600">
                <p>This usually happens when:</p>
                <ul class="list-disc list-inside mt-1">
                    <li>You've been inactive for too long</li>
                    <li>You have multiple tabs open</li>
                    <li>Your browser cache needs clearing</li>
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <!-- Script pour gérer les erreurs CSRF -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');

            if (form) {
                form.addEventListener('submit', function(e) {
                    // Vérifier si le token CSRF existe
                    const csrfToken = document.querySelector('input[name="_token"]');
                    if (!csrfToken || !csrfToken.value) {
                        e.preventDefault();
                        alert('Security token missing. Please refresh the page and try again.');
                        window.location.reload();
                        return false;
                    }
                });
            }

            // Auto-refresh du token CSRF toutes les 30 minutes
            setInterval(function() {
                fetch('/csrf-token')
                    .then(response => response.json())
                    .then(data => {
                        const tokenInput = document.querySelector('input[name="_token"]');
                        if (tokenInput && data.csrf_token) {
                            tokenInput.value = data.csrf_token;
                        }
                    })
                    .catch(error => {
                        console.log('CSRF token refresh failed:', error);
                    });
            }, 30 * 60 * 1000); // 30 minutes
        });
    </script>
</x-guest-layout>

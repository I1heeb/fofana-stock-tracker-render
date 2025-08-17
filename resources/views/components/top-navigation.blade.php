{{-- Top Navigation Component - Always visible Dashboard and Logout --}}
<header class="bg-navy-900 shadow-lg sticky top-0 z-30">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Left side - Mobile menu button and Dashboard -->
            <div class="flex items-center">
                <!-- Mobile menu button -->
                <button @click="sidebarOpen = true" 
                        class="lg:hidden text-white hover:text-mustard-400 mr-4 p-2 rounded-md transition-colors duration-200"
                        aria-label="Ouvrir le menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                
                <!-- Dashboard link - Always visible -->
                <a href="{{ route('dashboard') }}" 
                   class="nav-link flex items-center {{ request()->routeIs('dashboard') ? 'text-mustard-400' : '' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                    </svg>
                    <span class="hidden sm:inline">{{ __('Dashboard') }}</span>
                    <span class="sm:hidden">{{ __('Home') }}</span>
                </a>
            </div>
            
            <!-- Right side - User Info and Logout -->
            <div class="flex items-center space-x-2 sm:space-x-4">
                <!-- User Info Display -->
                @auth
                    <div class="text-sm font-medium text-white hidden sm:block">
                        {{ auth()->user()->name }}
                        <span class="text-xs text-gray-300 ml-1">({{ auth()->user()->getRoleDisplayName() }})</span>
                    </div>
                    
                    <!-- Mobile user indicator -->
                    <div class="sm:hidden text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                @endauth

                <!-- Language Selector (optional) -->
                @if(Route::has('locale.switch'))
                <div class="relative hidden md:block">
                    <select onchange="window.location.href=this.value"
                            class="text-sm bg-navy-800 border-navy-700 text-white rounded-md focus:ring-mustard-500 focus:border-mustard-500 pr-8">
                        <option value="{{ route('locale.switch', 'en') }}" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>🇺🇸</option>
                        <option value="{{ route('locale.switch', 'fr') }}" {{ app()->getLocale() === 'fr' ? 'selected' : '' }}>🇫🇷</option>
                        <option value="{{ route('locale.switch', 'ar') }}" {{ app()->getLocale() === 'ar' ? 'selected' : '' }}>🇸🇦</option>
                    </select>
                </div>
                @endif

                <!-- Settings Dropdown (optional) -->
                @if(Route::has('profile.edit'))
                <div class="relative hidden lg:block">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white hover:text-mustard-400 focus:outline-none transition ease-in-out duration-150">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
                @endif

                <!-- Logout Button - Always visible -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="btn-secondary flex items-center btn-touch"
                            title="{{ __('Logout') }}">
                        <svg class="w-4 h-4 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span class="hidden sm:inline">{{ __('Logout') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

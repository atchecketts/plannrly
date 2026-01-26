@props(['title' => 'Plannrly', 'active' => 'home'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - {{ config('app.name', 'Plannrly') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full bg-surface-950 text-white">
    <div x-data="{ menuOpen: false }" class="flex flex-col h-full">
        <!-- Slide-out Menu Overlay -->
        <div x-show="menuOpen"
             x-cloak
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="menuOpen = false"
             class="fixed inset-0 bg-black/70 z-40"></div>

        <!-- Slide-out Menu -->
        <div x-show="menuOpen"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed inset-y-0 left-0 w-72 bg-surface-900 border-r border-surface-700 z-50 flex flex-col">

            <!-- Menu Header -->
            <div class="p-4 border-b border-surface-700 bg-gradient-to-r from-primary-600/20 to-transparent">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-brand-500 to-primary-600 rounded-full flex items-center justify-center text-white font-semibold shadow-lg shadow-primary-600/20">
                            {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-medium text-white">{{ auth()->user()->full_name }}</p>
                            @php
                                $primaryRole = auth()->user()->businessRoles->firstWhere('pivot.is_primary', true)
                                    ?? auth()->user()->businessRoles->first();
                            @endphp
                            <p class="text-xs text-brand-300">{{ $primaryRole?->name ?? 'Employee' }}</p>
                        </div>
                    </div>
                    <button @click="menuOpen = false" class="p-2 text-surface-400 hover:text-white rounded-lg hover:bg-surface-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Menu Items -->
            <div class="flex-1 overflow-y-auto py-2">
                <!-- Main Navigation -->
                <div class="px-3 mb-4">
                    <p class="px-3 text-xs font-semibold text-surface-500 uppercase tracking-wider mb-2">Main</p>
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $active === 'home' ? 'bg-primary-600/20 text-brand-300 border border-primary-600/30' : 'text-surface-300 hover:bg-surface-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('my-shifts.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $active === 'shifts' ? 'bg-primary-600/20 text-brand-300 border border-primary-600/30' : 'text-surface-300 hover:bg-surface-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        My Shifts
                    </a>
                    <a href="{{ route('time-clock.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $active === 'clock' ? 'bg-primary-600/20 text-brand-300 border border-primary-600/30' : 'text-surface-300 hover:bg-surface-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Time Clock
                    </a>
                    <a href="{{ route('my-swaps.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $active === 'swaps' ? 'bg-primary-600/20 text-brand-300 border border-primary-600/30' : 'text-surface-300 hover:bg-surface-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        Shift Swaps
                    </a>
                </div>

                <!-- Leave -->
                <div class="px-3 mb-4">
                    <p class="px-3 text-xs font-semibold text-surface-500 uppercase tracking-wider mb-2">Leave</p>
                    <a href="{{ route('my-leave.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-surface-300 hover:bg-surface-800 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        My Leave Requests
                    </a>
                    <a href="{{ route('my-leave.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-surface-300 hover:bg-surface-800 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Request Leave
                    </a>
                </div>

                <!-- Account -->
                <div class="px-3">
                    <p class="px-3 text-xs font-semibold text-surface-500 uppercase tracking-wider mb-2">Account</p>
                    <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-surface-300 hover:bg-surface-800 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-surface-300 hover:bg-surface-800 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Header -->
        <header class="bg-surface-900 border-b border-surface-700 px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <!-- Hamburger Menu Button -->
                    <button @click="menuOpen = true" class="p-2 -ml-2 text-surface-400 hover:text-white rounded-lg hover:bg-surface-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div>
                        <p class="text-sm font-medium text-white">{{ $title }}</p>
                        <p class="text-xs text-surface-500">{{ auth()->user()->tenant?->name ?? 'Plannrly' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-brand-500 to-primary-600 rounded-full flex items-center justify-center text-white text-xs font-semibold shadow-lg shadow-primary-600/20">
                        {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-4">
            @if (session('success'))
                <div class="mb-4 rounded-lg bg-success-500/10 border border-success-500/30 p-3">
                    <p class="text-sm font-medium text-success-400">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-lg bg-danger-500/10 border border-danger-500/30 p-3">
                    <p class="text-sm font-medium text-danger-400">{{ session('error') }}</p>
                </div>
            @endif

            {{ $slot }}
        </main>

        <!-- Bottom Navigation -->
        <nav class="bg-surface-900 border-t border-surface-700 px-2 py-2">
            <div class="flex justify-around">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center px-3 py-2 rounded-lg {{ $active === 'home' ? 'text-brand-400' : 'text-surface-500 hover:text-surface-300' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-xs mt-1">Home</span>
                </a>
                <a href="{{ route('my-shifts.index') }}" class="flex flex-col items-center px-3 py-2 rounded-lg {{ $active === 'shifts' ? 'text-brand-400' : 'text-surface-500 hover:text-surface-300' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-xs mt-1">Shifts</span>
                </a>
                <a href="{{ route('time-clock.index') }}" class="flex flex-col items-center px-3 py-2 rounded-lg {{ $active === 'clock' ? 'text-brand-400' : 'text-surface-500 hover:text-surface-300' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-xs mt-1">Clock</span>
                </a>
                <a href="{{ route('my-swaps.index') }}" class="flex flex-col items-center px-3 py-2 rounded-lg {{ $active === 'swaps' ? 'text-brand-400' : 'text-surface-500 hover:text-surface-300' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span class="text-xs mt-1">Swaps</span>
                </a>
            </div>
        </nav>
    </div>
</body>
</html>

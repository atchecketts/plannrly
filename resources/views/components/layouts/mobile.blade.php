@props(['title' => 'Plannrly', 'active' => 'home', 'showHeader' => true])

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
<body class="h-full bg-[#0f0f23] text-white">
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
             class="fixed inset-y-0 left-0 w-72 bg-[#1a1a2e] border-r border-gray-800 z-50 flex flex-col">

            <!-- Menu Header -->
            <div class="p-4 border-b border-gray-800 bg-gradient-to-r from-indigo-900 to-purple-900">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center text-white font-semibold">
                            {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-medium text-white">{{ auth()->user()->full_name }}</p>
                            @php
                                $primaryRole = auth()->user()->businessRoles->firstWhere('pivot.is_primary', true)
                                    ?? auth()->user()->businessRoles->first();
                            @endphp
                            <p class="text-xs text-indigo-200">{{ $primaryRole?->name ?? 'Employee' }}</p>
                        </div>
                    </div>
                    <button @click="menuOpen = false" class="p-2 text-white/70 hover:text-white rounded-lg hover:bg-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Menu Items -->
            <div class="flex-1 overflow-y-auto py-2">
                <div class="px-3 space-y-1">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $active === 'home' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Home
                    </a>
                    <a href="{{ route('my-shifts.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $active === 'shifts' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Shifts
                    </a>
                    <a href="{{ route('time-clock.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $active === 'clock' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Clock In/Out
                    </a>
                    <a href="{{ route('my-swaps.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $active === 'swaps' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        Swap
                    </a>
                    <a href="{{ route('my-leave.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $active === 'leave' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Leave
                    </a>
                    <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $active === 'profile' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Profile
                    </a>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-800 px-3">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto pb-20">
            @if (session('success'))
                <div class="mx-4 mt-4 mb-4 rounded-lg bg-green-500/10 border border-green-500/30 p-3">
                    <p class="text-sm font-medium text-green-400">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mx-4 mt-4 mb-4 rounded-lg bg-red-500/10 border border-red-500/30 p-3">
                    <p class="text-sm font-medium text-red-400">{{ session('error') }}</p>
                </div>
            @endif

            {{ $slot }}
        </main>

        <!-- Bottom Navigation -->
        <nav class="fixed bottom-0 left-0 right-0 bg-[#1a1a2e] border-t border-gray-800 px-4 py-2 z-30">
            <div class="flex justify-around items-end">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-1 {{ $active === 'home' ? 'text-indigo-400' : 'text-gray-500' }}">
                    <svg class="w-6 h-6" fill="{{ $active === 'home' ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-xs mt-1">Home</span>
                </a>
                <a href="{{ route('my-shifts.index') }}" class="flex flex-col items-center py-1 {{ $active === 'shifts' ? 'text-indigo-400' : 'text-gray-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-xs mt-1">Shifts</span>
                </a>
                <a href="{{ route('time-clock.index') }}" class="flex flex-col items-center -mt-4">
                    <div class="w-14 h-14 bg-indigo-600 rounded-full flex items-center justify-center shadow-lg shadow-indigo-600/50 {{ $active === 'clock' ? 'ring-2 ring-indigo-400' : '' }}">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xs mt-1 {{ $active === 'clock' ? 'text-indigo-400' : 'text-gray-500' }}">Clock</span>
                </a>
                <a href="{{ route('my-swaps.index') }}" class="flex flex-col items-center py-1 {{ $active === 'swaps' ? 'text-indigo-400' : 'text-gray-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span class="text-xs mt-1">Swap</span>
                </a>
                <a href="{{ route('profile.show') }}" class="flex flex-col items-center py-1 {{ $active === 'profile' ? 'text-indigo-400' : 'text-gray-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-xs mt-1">Profile</span>
                </a>
            </div>
        </nav>
    </div>
</body>
</html>

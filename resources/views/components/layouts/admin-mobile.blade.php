@props(['title' => 'Plannrly', 'active' => 'home', 'showHeader' => true, 'headerTitle' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ $title }} - {{ config('app.name', 'Plannrly') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .safe-area-bottom { padding-bottom: env(safe-area-inset-bottom, 0); }
        .safe-area-top { padding-top: env(safe-area-inset-top, 0); }
        [x-cloak] { display: none !important; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-gray-950">
    <div class="flex flex-col h-full bg-gray-950">
        <!-- Status Bar Spacer -->
        <div class="bg-brand-900 h-6 safe-area-top"></div>

        @if($showHeader)
        <!-- Header -->
        <header class="bg-brand-900 text-white px-4 pb-6 pt-2">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center font-medium">
                        {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                    </div>
                    <div>
                        <p class="font-semibold">Hello, {{ auth()->user()->first_name }}!</p>
                        <p class="text-sm text-brand-200">
                            {{ auth()->user()->getHighestRole()?->label() ?? 'Admin' }}
                        </p>
                    </div>
                </div>
                <x-mobile-menu :isAdmin="true" />
            </div>
            @if($headerTitle)
            <h1 class="text-xl font-bold">{{ $headerTitle }}</h1>
            @endif
        </header>
        @endif

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            @if (session('success'))
                <div class="mx-4 mt-4 rounded-lg bg-green-500/10 border border-green-500/20 p-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <p class="text-sm font-medium text-green-400">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mx-4 mt-4 rounded-lg bg-red-500/10 border border-red-500/20 p-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <p class="text-sm font-medium text-red-400">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{ $slot }}
        </main>

        <!-- Bottom Navigation -->
        <nav class="bg-gray-900 border-t border-gray-800 safe-area-bottom">
            <div class="grid grid-cols-5 h-16">
                <a href="{{ route('dashboard') }}"
                   class="flex flex-col items-center justify-center {{ $active === 'home' ? 'text-brand-400' : 'text-gray-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-xs mt-1 {{ $active === 'home' ? 'font-medium' : '' }}">Home</span>
                </a>
                <a href="{{ route('schedule.mobile') }}"
                   class="flex flex-col items-center justify-center {{ $active === 'schedule' ? 'text-brand-400' : 'text-gray-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-xs mt-1 {{ $active === 'schedule' ? 'font-medium' : '' }}">Schedule</span>
                </a>
                <a href="{{ route('leave-requests.mobile') }}"
                   class="flex flex-col items-center justify-center">
                    <div class="w-14 h-14 -mt-8 bg-brand-900 rounded-full flex items-center justify-center shadow-lg border-4 border-gray-950">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <span class="text-xs mt-1 text-brand-400 font-medium">Requests</span>
                </a>
                <a href="{{ route('users.mobile') }}"
                   class="flex flex-col items-center justify-center {{ $active === 'team' ? 'text-brand-400' : 'text-gray-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="text-xs mt-1 {{ $active === 'team' ? 'font-medium' : '' }}">Team</span>
                </a>
                <a href="{{ route('profile.show') }}"
                   class="flex flex-col items-center justify-center {{ $active === 'profile' ? 'text-brand-400' : 'text-gray-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-xs mt-1 {{ $active === 'profile' ? 'font-medium' : '' }}">Profile</span>
                </a>
            </div>
        </nav>
    </div>
</body>
</html>

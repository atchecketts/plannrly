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
</head>
<body class="h-full bg-gray-950 text-white">
    <div class="flex flex-col h-full">
        <!-- Header -->
        <header class="bg-gray-900 border-b border-gray-800 px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-brand-900 rounded-full flex items-center justify-center text-white text-sm font-medium">
                        {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white">{{ auth()->user()->first_name }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->getHighestRole()?->label() ?? 'Admin' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('profile.show') }}" class="p-2 text-gray-400 hover:text-white rounded-lg hover:bg-gray-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-4">
            @if (session('success'))
                <div class="mb-4 rounded-lg bg-green-500/10 border border-green-500/20 p-3">
                    <p class="text-sm font-medium text-green-400">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-lg bg-red-500/10 border border-red-500/20 p-3">
                    <p class="text-sm font-medium text-red-400">{{ session('error') }}</p>
                </div>
            @endif

            {{ $slot }}
        </main>

        <!-- Bottom Navigation -->
        <nav class="bg-gray-900 border-t border-gray-800 px-2 py-2">
            <div class="flex justify-around">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center px-3 py-2 rounded-lg {{ $active === 'home' ? 'text-brand-400' : 'text-gray-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-xs mt-1">Home</span>
                </a>
                <a href="{{ route('schedule.index') }}" class="flex flex-col items-center px-3 py-2 rounded-lg {{ $active === 'schedule' ? 'text-brand-400' : 'text-gray-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-xs mt-1">Schedule</span>
                </a>
                <a href="{{ route('users.index') }}" class="flex flex-col items-center px-3 py-2 rounded-lg {{ $active === 'team' ? 'text-brand-400' : 'text-gray-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="text-xs mt-1">Team</span>
                </a>
                <a href="{{ route('leave-requests.index') }}" class="flex flex-col items-center px-3 py-2 rounded-lg {{ $active === 'requests' ? 'text-brand-400' : 'text-gray-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span class="text-xs mt-1">Requests</span>
                </a>
            </div>
        </nav>
    </div>
</body>
</html>

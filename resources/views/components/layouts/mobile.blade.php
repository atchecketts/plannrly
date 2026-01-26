@props(['title' => 'Plannrly', 'active' => 'home', 'showHeader' => true, 'headerTitle' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
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
        .safe-area-bottom { padding-bottom: env(safe-area-inset-bottom, 0); }
        .safe-area-top { padding-top: env(safe-area-inset-top, 0); }
        [x-cloak] { display: none !important; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-gray-950 text-white">
    <div class="flex flex-col h-full max-w-md mx-auto bg-gray-950 relative">
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
                            @php
                                $primaryRole = auth()->user()->businessRoles->firstWhere('pivot.is_primary', true)
                                    ?? auth()->user()->businessRoles->first();
                            @endphp
                            @if($primaryRole)
                                @if($primaryRole->department)
                                    {{ $primaryRole->department->name }} &bull;
                                @endif
                                {{ $primaryRole->name }}
                            @endif
                        </p>
                    </div>
                </div>
                <x-mobile-menu :isAdmin="false" />
            </div>
            @if($headerTitle)
            <h1 class="text-xl font-bold">{{ $headerTitle }}</h1>
            @endif
        </header>
        @endif

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto pb-20">
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
        <x-bottom-nav :active="$active" />
    </div>
</body>
</html>

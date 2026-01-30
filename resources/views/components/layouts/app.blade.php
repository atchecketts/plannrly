@props(['title' => 'Dashboard', 'header' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - {{ config('app.name', 'Plannrly') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-950 text-white">
    @if(session('impersonator_id'))
        <div class="bg-amber-500 text-black px-4 py-2">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="font-medium">You are impersonating {{ auth()->user()->full_name }}</span>
                    <span class="text-amber-800 text-sm">(logged in as {{ session('impersonator_name') }})</span>
                </div>
                <form method="POST" action="{{ route('impersonate.stop') }}">
                    @csrf
                    <button type="submit" class="bg-black text-white px-4 py-1.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
                        Stop Impersonating
                    </button>
                </form>
            </div>
        </div>
    @endif
    <div class="min-h-full flex">
        <!-- Sidebar -->
        <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-gray-900 border-r border-gray-800">
            <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-800">
                <x-logo class="h-8" />
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'flex items-center gap-3 px-3 py-2.5 text-white bg-brand-900/50 border border-brand-700/50 rounded-lg' : 'flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="font-medium">Dashboard</span>
                </a>

                @if(auth()->user()->isSuperAdmin())
                    <div class="pt-4 mt-4 border-t border-gray-800">
                        <p class="px-3 text-xs font-semibold text-amber-500 uppercase tracking-wider">Super Admin</p>
                    </div>

                    <a href="{{ route('super-admin.tenants.index') }}" class="{{ request()->routeIs('super-admin.tenants.*') ? 'flex items-center gap-3 px-3 py-2.5 text-white bg-brand-900/50 border border-brand-700/50 rounded-lg' : 'flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>Tenants</span>
                    </a>

                    <a href="{{ route('super-admin.users.index') }}" class="{{ request()->routeIs('super-admin.users.*') ? 'flex items-center gap-3 px-3 py-2.5 text-white bg-brand-900/50 border border-brand-700/50 rounded-lg' : 'flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>All Users</span>
                    </a>

                    <div class="pt-4 mt-4 border-t border-gray-800">
                        <p class="px-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Tenant</p>
                    </div>
                @endif

                <a href="{{ route('schedule.index') }}" class="{{ request()->routeIs('schedule.*') ? 'flex items-center gap-3 px-3 py-2.5 text-white bg-brand-900/50 border border-brand-700/50 rounded-lg' : 'flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Schedule</span>
                </a>

                @php
                    $clockInEnabled = \App\Models\TenantSettings::where('tenant_id', auth()->user()->tenant_id)->first()?->enable_clock_in_out ?? false;
                @endphp
                @if($clockInEnabled)
                <a href="{{ route('time-entries.index') }}" class="{{ request()->routeIs('time-entries.*') ? 'flex items-center gap-3 px-3 py-2.5 text-white bg-brand-900/50 border border-brand-700/50 rounded-lg' : 'flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Time Clock</span>
                </a>
                <a href="{{ auth()->user()->isEmployee() ? route('timesheets.employee') : route('timesheets.index') }}" class="{{ request()->routeIs('timesheets.*') ? 'flex items-center gap-3 px-3 py-2.5 text-white bg-brand-900/50 border border-brand-700/50 rounded-lg' : 'flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Timesheets</span>
                </a>
                @endif

                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'flex items-center gap-3 px-3 py-2.5 text-white bg-brand-900/50 border border-brand-700/50 rounded-lg' : 'flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Employees</span>
                </a>

                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                <a href="{{ route('locations.index') }}" class="{{ request()->routeIs('locations.*') ? 'flex items-center gap-3 px-3 py-2.5 text-white bg-brand-900/50 border border-brand-700/50 rounded-lg' : 'flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Locations</span>
                </a>
                @endif

                <a href="{{ route('departments.index') }}" class="{{ request()->routeIs('departments.*') ? 'flex items-center gap-3 px-3 py-2.5 text-white bg-brand-900/50 border border-brand-700/50 rounded-lg' : 'flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Departments</span>
                </a>

                <a href="{{ route('business-roles.index') }}" class="{{ request()->routeIs('business-roles.*') ? 'flex items-center gap-3 px-3 py-2.5 text-white bg-brand-900/50 border border-brand-700/50 rounded-lg' : 'flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Business Roles</span>
                </a>

                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                <a href="{{ route('staffing-requirements.index') }}" class="{{ request()->routeIs('staffing-requirements.*') ? 'flex items-center gap-3 px-3 py-2.5 text-white bg-brand-900/50 border border-brand-700/50 rounded-lg' : 'flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Staffing Needs</span>
                </a>
                @endif

                <div class="pt-4 mt-4 border-t border-gray-800">
                    <p class="px-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Management</p>
                </div>

                <a href="{{ route('leave-requests.index') }}" class="{{ request()->routeIs('leave-requests.*') ? 'flex items-center gap-3 px-3 py-2.5 text-white bg-brand-900/50 border border-brand-700/50 rounded-lg' : 'flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Leave Requests</span>
                </a>

                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                <a href="{{ route('leave-types.index') }}" class="{{ request()->routeIs('leave-types.*') ? 'flex items-center gap-3 px-3 py-2.5 text-white bg-brand-900/50 border border-brand-700/50 rounded-lg' : 'flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span>Leave Types</span>
                </a>
                <a href="{{ route('leave-allowances.index') }}" class="{{ request()->routeIs('leave-allowances.*') ? 'flex items-center gap-3 px-3 py-2.5 text-white bg-brand-900/50 border border-brand-700/50 rounded-lg' : 'flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Leave Allowances</span>
                </a>
                @endif

                <a href="{{ route('shift-swaps.index') }}" class="{{ request()->routeIs('shift-swaps.*') ? 'flex items-center gap-3 px-3 py-2.5 text-white bg-brand-900/50 border border-brand-700/50 rounded-lg' : 'flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span>Shift Swaps</span>
                </a>

                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                <a href="{{ route('settings.edit') }}" class="{{ request()->routeIs('settings.*') ? 'flex items-center gap-3 px-3 py-2.5 text-white bg-brand-900/50 border border-brand-700/50 rounded-lg' : 'flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Settings</span>
                </a>

                <a href="{{ route('subscription.index') }}" class="{{ request()->routeIs('subscription.*') ? 'flex items-center gap-3 px-3 py-2.5 text-white bg-brand-900/50 border border-brand-700/50 rounded-lg' : 'flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span>Subscription</span>
                </a>
                @endif
            </nav>

            <!-- User Menu -->
            <div class="p-4 border-t border-gray-800" x-data="{ open: false }">
                <div class="relative">
                    <button @click="open = !open" class="w-full flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800 transition-colors">
                        @if(auth()->user()->avatar_path)
                            <img src="{{ Storage::url(auth()->user()->avatar_path) }}" alt="{{ auth()->user()->full_name }}" class="w-10 h-10 rounded-full object-cover">
                        @else
                            <div class="w-10 h-10 bg-brand-900 rounded-full flex items-center justify-center text-white font-medium">
                                {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                            </div>
                        @endif
                        <div class="flex-1 min-w-0 text-left">
                            <p class="text-sm font-medium text-white truncate">{{ auth()->user()->full_name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->getHighestRole()?->label() ?? 'Employee' }}</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition class="absolute bottom-full left-0 right-0 mb-2 bg-gray-800 border border-gray-700 rounded-lg shadow-xl z-50">
                        <div class="py-1">
                            <a href="{{ route('profile.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                My Profile
                            </a>
                            <a href="{{ route('availability.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                My Availability
                            </a>
                            <div class="border-t border-gray-700 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen bg-gray-950">
            <!-- Top Header -->
            <header class="bg-gray-900 border-b border-gray-800 px-6 py-4">
                <div class="flex items-center justify-between">
                    @if(isset($header))
                        {{ $header }}
                    @else
                        <div>
                            <h1 class="text-2xl font-bold text-white">{{ $title }}</h1>
                        </div>
                    @endif
                    <div class="flex items-center gap-4">
                        <!-- Notification Bell -->
                        <x-notification-bell />
                        <!-- Mobile menu button -->
                        <div class="lg:hidden">
                            <x-logo class="h-8" />
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 p-6 overflow-y-auto">
                @if (session('success'))
                    <div class="mb-6 rounded-lg bg-green-500/10 border border-green-500/30 p-4">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <p class="text-sm font-medium text-green-400">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 rounded-lg bg-red-500/10 border border-red-500/30 p-4">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <p class="text-sm font-medium text-red-400">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>

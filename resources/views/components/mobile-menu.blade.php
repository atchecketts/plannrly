@props(['isAdmin' => false])

<!-- Mobile Menu Overlay -->
<div x-data="{ open: false }" x-cloak>
    <!-- Menu Button (placed in header) -->
    <button @click="open = true" class="relative p-2 bg-white/10 rounded-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <!-- Backdrop -->
    <div x-show="open"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="fixed inset-0 bg-black/60 z-40">
    </div>

    <!-- Slide-out Menu -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-y-0 left-0 w-72 bg-gray-900 z-50 overflow-y-auto">

        <!-- Menu Header -->
        <div class="px-4 py-6 bg-brand-900">
            <div class="flex items-center justify-between mb-4">
                <x-logo class="h-8" />
                <button @click="open = false" class="p-2 text-white/70 hover:text-white rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center font-medium text-lg">
                    {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                </div>
                <div>
                    <p class="font-semibold text-white">{{ auth()->user()->full_name }}</p>
                    <p class="text-sm text-brand-200">{{ auth()->user()->getHighestRole()?->label() ?? 'Employee' }}</p>
                </div>
            </div>
        </div>

        <!-- Menu Items -->
        <nav class="px-3 py-4">
            @if($isAdmin)
                <!-- Admin Navigation -->
                <div class="space-y-1">
                    <p class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Main</p>

                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('schedule.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Schedule</span>
                    </a>

                    <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Employees</span>
                    </a>
                </div>

                <div class="mt-4 space-y-1">
                    <p class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Organization</p>

                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                    <a href="{{ route('locations.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>Locations</span>
                    </a>
                    @endif

                    <a href="{{ route('departments.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span>Departments</span>
                    </a>

                    <a href="{{ route('business-roles.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>Business Roles</span>
                    </a>
                </div>

                <div class="mt-4 space-y-1">
                    <p class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Management</p>

                    <a href="{{ route('leave-requests.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Leave Requests</span>
                    </a>

                    <a href="{{ route('shift-swaps.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        <span>Shift Swaps</span>
                    </a>
                </div>

            @else
                <!-- Employee Navigation -->
                <div class="space-y-1">
                    <p class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Main</p>

                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Home</span>
                    </a>

                    <a href="{{ route('my-shifts.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>My Shifts</span>
                    </a>

                    <a href="{{ route('time-clock.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Time Clock</span>
                    </a>
                </div>

                <div class="mt-4 space-y-1">
                    <p class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Requests</p>

                    <a href="{{ route('my-swaps.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        <span>Shift Swaps</span>
                    </a>

                    <a href="{{ route('my-leave.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>My Leave</span>
                    </a>

                    <a href="{{ route('my-leave.create') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Request Leave</span>
                    </a>
                </div>
            @endif

            <!-- Account Section (Common) -->
            <div class="mt-4 pt-4 border-t border-gray-800 space-y-1">
                <p class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Account</p>

                <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profile</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 text-red-400 hover:text-red-300 hover:bg-gray-800 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Sign Out</span>
                    </button>
                </form>
            </div>
        </nav>
    </div>
</div>

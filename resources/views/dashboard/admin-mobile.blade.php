<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - {{ config('app.name', 'Plannrly') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .safe-area-bottom { padding-bottom: env(safe-area-inset-bottom, 0); }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f0ff',
                            100: '#e0e0ff',
                            200: '#c4c0ff',
                            300: '#a090ff',
                            400: '#7c5cff',
                            500: '#5a30f0',
                            600: '#4a20d0',
                            700: '#3a15b0',
                            800: '#2a0fa0',
                            900: '#160092',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full bg-gray-950">
    <div class="flex flex-col h-full bg-gray-950">
        <!-- Status Bar Spacer -->
        <div class="bg-brand-900 h-6"></div>

        <!-- Header -->
        <header class="bg-brand-900 text-white px-4 pb-6 pt-2">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center font-medium">
                        {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                    </div>
                    <div>
                        <p class="font-semibold">Hello, {{ auth()->user()->first_name }}!</p>
                        <p class="text-sm text-brand-200">{{ auth()->user()->getHighestRole()?->label() ?? 'Admin' }}</p>
                    </div>
                </div>
                <button class="relative p-2 bg-white/10 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <!-- Team Overview Card -->
            <div class="px-4 -mt-4">
                <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
                    <div class="p-4 border-b border-gray-800">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-sm text-gray-500">Team Overview</p>
                                <p class="font-semibold text-white">{{ now()->format('l, M d') }}</p>
                            </div>
                            @if($stats['unassigned_shifts'] > 0)
                                <span class="px-3 py-1 bg-red-500/20 text-red-400 text-sm font-medium rounded-full">{{ $stats['unassigned_shifts'] }} Unassigned</span>
                            @else
                                <span class="px-3 py-1 bg-green-500/20 text-green-400 text-sm font-medium rounded-full">All Covered</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <div class="text-2xl font-bold text-white">{{ $stats['on_duty_today'] }} on duty</div>
                                <div class="text-sm text-gray-500 mt-1">{{ $stats['on_leave_today'] }} on leave today</div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Section -->
                    <div class="p-4 bg-gray-800/50">
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="text-center p-3 bg-gray-900 rounded-xl border border-gray-800">
                                <p class="text-xs text-gray-500 mb-1">This Week</p>
                                <p class="text-lg font-bold text-brand-400">{{ $stats['hours_this_week'] }}h</p>
                            </div>
                            <div class="text-center p-3 bg-gray-900 rounded-xl border border-gray-800">
                                <p class="text-xs text-gray-500 mb-1">Total Shifts</p>
                                <p class="text-lg font-bold text-white">{{ $stats['total_shifts_this_week'] }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <a href="{{ route('schedule.mobile') }}" class="flex items-center justify-center gap-2 py-3 bg-brand-900 text-white rounded-xl font-medium">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Schedule
                            </a>
                            <a href="{{ route('users.create') }}" class="flex items-center justify-center gap-2 py-3 bg-green-600 text-white rounded-xl font-medium">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                Add Staff
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Shifts -->
            <div class="px-4 mt-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Today's Shifts</h2>
                    <a href="{{ route('schedule.day') }}" class="text-sm text-brand-400 font-medium">View All</a>
                </div>

                @if($todayShifts->isEmpty())
                    <div class="bg-gray-900 rounded-xl p-6 border border-gray-800 text-center">
                        <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500">No shifts scheduled for today</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($todayShifts->take(5) as $shift)
                            <div class="bg-gray-900 rounded-xl p-4 border border-gray-800 flex items-center gap-4">
                                @if($shift->user)
                                    <div class="w-12 h-12 bg-brand-900/50 rounded-xl flex items-center justify-center border border-brand-700/50">
                                        <span class="text-sm font-bold text-brand-400">{{ substr($shift->user->first_name, 0, 1) }}{{ substr($shift->user->last_name, 0, 1) }}</span>
                                    </div>
                                @else
                                    <div class="w-12 h-12 bg-red-500/20 rounded-xl flex items-center justify-center border border-red-500/30">
                                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    @if($shift->user)
                                        <p class="font-medium text-white">{{ $shift->user->full_name }}</p>
                                    @else
                                        <p class="font-medium text-red-400">Unassigned Shift</p>
                                    @endif
                                    <p class="text-sm text-gray-500">{{ $shift->department?->name }}</p>
                                </div>
                                <span class="text-sm text-gray-500">{{ $shift->start_time->format('g:i A') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Pending Leave Requests -->
            @if($pendingLeave->isNotEmpty())
                <div class="px-4 mt-6">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Pending Leave</h2>
                        <a href="{{ route('leave-requests.mobile') }}" class="text-sm text-brand-400 font-medium">View All</a>
                    </div>
                    <div class="space-y-3">
                        @foreach($pendingLeave->take(3) as $leave)
                            <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-amber-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-amber-300">{{ $leave->user->full_name }}</p>
                                        <p class="text-sm text-amber-400/70">{{ $leave->start_date->format('M d') }} - {{ $leave->leaveType->name }}</p>
                                        <p class="text-xs text-amber-400/50 mt-1">Awaiting approval</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="px-4 mt-6 mb-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Quick Actions</h2>
                <div class="bg-gray-900 rounded-xl p-4 border border-gray-800">
                    <div class="grid grid-cols-4 gap-4">
                        <a href="{{ route('schedule.index') }}" class="flex flex-col items-center gap-2">
                            <div class="w-12 h-12 bg-brand-500/10 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <span class="text-xs text-gray-400">Schedule</span>
                        </a>
                        <a href="{{ route('users.mobile') }}" class="flex flex-col items-center gap-2">
                            <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <span class="text-xs text-gray-400">Team</span>
                        </a>
                        <a href="{{ route('shift-swaps.index') }}" class="flex flex-col items-center gap-2">
                            <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                            </div>
                            <span class="text-xs text-gray-400">Swaps</span>
                        </a>
                        <a href="{{ route('departments.index') }}" class="flex flex-col items-center gap-2">
                            <div class="w-12 h-12 bg-purple-500/10 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </div>
                            <span class="text-xs text-gray-400">Depts</span>
                        </a>
                    </div>
                </div>
            </div>

        </main>

        <!-- Bottom Navigation -->
        <nav class="bg-gray-900 border-t border-gray-800 safe-area-bottom">
            <div class="grid grid-cols-5 h-16">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center text-brand-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-xs mt-1 font-medium">Home</span>
                </a>
                <a href="{{ route('schedule.mobile') }}" class="flex flex-col items-center justify-center text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-xs mt-1">Schedule</span>
                </a>
                <a href="{{ route('leave-requests.mobile') }}" class="flex flex-col items-center justify-center">
                    <div class="w-14 h-14 -mt-8 bg-brand-900 rounded-full flex items-center justify-center shadow-lg border-4 border-gray-950">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <span class="text-xs mt-1 text-brand-400 font-medium">Requests</span>
                </a>
                <a href="{{ route('users.mobile') }}" class="flex flex-col items-center justify-center text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="text-xs mt-1">Team</span>
                </a>
                <a href="{{ route('profile.show') }}" class="flex flex-col items-center justify-center text-gray-500">
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

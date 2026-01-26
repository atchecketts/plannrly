<x-layouts.admin-mobile title="Dashboard" active="home">
    <!-- Stats Grid -->
    <div class="grid grid-cols-2 gap-3 mb-6">
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider">On Duty</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['on_duty_today'] }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider">On Leave</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['on_leave_today'] }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Hours This Week</p>
            <p class="text-2xl font-bold text-brand-400 mt-1">{{ $stats['hours_this_week'] }}h</p>
        </div>
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Unassigned</p>
            <p class="text-2xl font-bold {{ $stats['unassigned_shifts'] > 0 ? 'text-red-400' : 'text-green-400' }} mt-1">{{ $stats['unassigned_shifts'] }}</p>
        </div>
    </div>

    <!-- Today's Shifts -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Today's Shifts</h2>
            <a href="{{ route('schedule.mobile') }}" class="text-sm text-brand-400">View All</a>
        </div>

        @if($todayShifts->isEmpty())
            <div class="bg-gray-900 rounded-lg border border-gray-800 p-6 text-center">
                <svg class="w-8 h-8 text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-sm text-gray-500">No shifts scheduled for today</p>
            </div>
        @else
            <div class="space-y-2">
                @foreach($todayShifts->take(5) as $shift)
                    <div class="bg-gray-900 rounded-lg border {{ $shift->user_id ? 'border-gray-800' : 'border-red-500/50' }} p-3 flex items-center gap-3">
                        @if($shift->user)
                            <div class="w-10 h-10 bg-brand-900/50 rounded-full flex items-center justify-center text-brand-400 text-sm font-medium">
                                {{ substr($shift->user->first_name, 0, 1) }}{{ substr($shift->user->last_name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white truncate">{{ $shift->user->full_name }}</p>
                                <p class="text-xs text-gray-500">{{ $shift->department?->name }}</p>
                            </div>
                        @else
                            <div class="w-10 h-10 bg-red-500/20 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-red-400">Unassigned</p>
                                <p class="text-xs text-gray-500">{{ $shift->department?->name }}</p>
                            </div>
                        @endif
                        <div class="text-right">
                            <p class="text-sm text-white">{{ $shift->start_time->format('g:ia') }}</p>
                            <p class="text-xs text-gray-500">{{ $shift->working_hours }}h</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Pending Leave Requests -->
    @if($pendingLeave->isNotEmpty())
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Pending Leave</h2>
                <a href="{{ route('leave-requests.index') }}" class="text-sm text-brand-400">View All</a>
            </div>
            <div class="space-y-2">
                @foreach($pendingLeave->take(3) as $leave)
                    <div class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-amber-300">{{ $leave->user->full_name }}</p>
                                <p class="text-xs text-amber-400/70">{{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d') }}</p>
                            </div>
                            <span class="text-xs text-amber-400 bg-amber-500/20 px-2 py-1 rounded">{{ $leave->leaveType->name }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Pending Swaps -->
    @if($pendingSwaps->isNotEmpty())
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Pending Swaps</h2>
                <a href="{{ route('shift-swaps.index') }}" class="text-sm text-brand-400">View All</a>
            </div>
            <div class="space-y-2">
                @foreach($pendingSwaps->take(3) as $swap)
                    <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-3">
                        <p class="text-sm font-medium text-blue-300">{{ $swap->requestingShift->user->full_name }}</p>
                        <p class="text-xs text-blue-400/70">{{ $swap->requestingShift->date->format('D, M d') }} - {{ $swap->requestingShift->start_time->format('g:ia') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Quick Actions -->
    <div>
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Quick Actions</h2>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('schedule.mobile') }}" class="bg-gray-900 rounded-lg border border-gray-800 p-4 flex items-center gap-3">
                <div class="w-10 h-10 bg-brand-900/50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <span class="text-sm text-white">Schedule</span>
            </a>
            <a href="{{ route('users.create') }}" class="bg-gray-900 rounded-lg border border-gray-800 p-4 flex items-center gap-3">
                <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <span class="text-sm text-white">Add Staff</span>
            </a>
        </div>
    </div>
</x-layouts.admin-mobile>

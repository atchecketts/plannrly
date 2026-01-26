<x-layouts.admin-mobile title="Dashboard" active="home">
    <!-- Main Content -->
    <div class="px-4 pt-4">
        <!-- Overview Card (like employee's Today's Shift Card) -->
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
                    <a href="{{ route('schedule.mobile') }}" class="flex items-center justify-center gap-2 py-3 bg-brand-900 text-white rounded-xl font-medium hover:bg-brand-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Schedule
                    </a>
                    <a href="{{ route('users.create') }}" class="flex items-center justify-center gap-2 py-3 bg-green-600 text-white rounded-xl font-medium hover:bg-green-500 transition-colors">
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
            <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6 text-center">
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
                    <div class="bg-gray-900 rounded-2xl p-4 border {{ $shift->user_id ? 'border-gray-800' : 'border-red-500/30 border-dashed' }} flex items-center gap-4">
                        @if($shift->user)
                            <div class="w-12 h-12 bg-brand-900/50 rounded-xl flex flex-col items-center justify-center border border-brand-700/50">
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
                            <p class="text-sm text-gray-500">{{ $shift->department?->name }} @if($shift->businessRole)&bull; {{ $shift->businessRole->name }}@endif</p>
                        </div>
                        <span class="text-sm text-gray-500">{{ $shift->start_time->format('g:i A') }}</span>
                    </div>
                @endforeach
                @if($todayShifts->count() > 5)
                    <a href="{{ route('schedule.day') }}" class="block text-center py-3 text-sm text-brand-400 font-medium">
                        + {{ $todayShifts->count() - 5 }} more shifts
                    </a>
                @endif
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
                    <div class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-amber-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-amber-300">{{ $leave->user->full_name }}</p>
                                <p class="text-sm text-amber-400/70">{{ $leave->start_date->format('M d') }}@if(!$leave->start_date->eq($leave->end_date))-{{ $leave->end_date->format('d') }}@endif ({{ $leave->leaveType->name }})</p>
                                <p class="text-xs text-amber-400/50 mt-1">Awaiting approval</p>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-3">
                            <form action="{{ route('leave-requests.review', $leave) }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="w-full py-2 bg-green-500 text-white text-sm font-medium rounded-lg hover:bg-green-400 transition-colors">
                                    Approve
                                </button>
                            </form>
                            <form action="{{ route('leave-requests.review', $leave) }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="w-full py-2 bg-gray-700 text-white text-sm font-medium rounded-lg hover:bg-gray-600 transition-colors">
                                    Reject
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Pending Shift Swaps -->
    @if($pendingSwaps->isNotEmpty())
        <div class="px-4 mt-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Pending Swaps</h2>
                <a href="{{ route('shift-swaps.index') }}" class="text-sm text-brand-400 font-medium">View All</a>
            </div>
            <div class="space-y-3">
                @foreach($pendingSwaps->take(3) as $swap)
                    <div class="bg-blue-500/10 border border-blue-500/30 rounded-2xl p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-blue-300">{{ $swap->requestingShift->user->full_name }}</p>
                                <p class="text-sm text-blue-400/70">{{ $swap->requestingShift->date->format('D, M d') }} &bull; {{ $swap->requestingShift->start_time->format('g:i A') }}</p>
                                <p class="text-xs text-blue-400/50 mt-1">Swap request pending</p>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-3">
                            <form action="{{ route('shift-swaps.approve', $swap) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full py-2 bg-green-500 text-white text-sm font-medium rounded-lg hover:bg-green-400 transition-colors">
                                    Approve
                                </button>
                            </form>
                            <form action="{{ route('shift-swaps.reject', $swap) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full py-2 bg-gray-700 text-white text-sm font-medium rounded-lg hover:bg-gray-600 transition-colors">
                                    Reject
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Quick Actions -->
    <div class="px-4 mt-6 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Quick Actions</h2>
        </div>
        <div class="bg-gray-900 rounded-2xl p-4 border border-gray-800">
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

    <div class="h-6"></div>
</x-layouts.admin-mobile>

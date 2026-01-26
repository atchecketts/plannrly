<x-layouts.mobile title="Dashboard" active="home">
    @php
        $primaryRole = $user->businessRoles->firstWhere('pivot.is_primary', true)
            ?? $user->businessRoles->first();
    @endphp

    <!-- Today's Shift Card -->
    <div class="mb-6">
        @if($todayShift)
            <div class="bg-gray-900 rounded-lg border border-gray-800 overflow-hidden">
                <div class="p-4 border-b border-gray-800">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Today's Shift</p>
                            <p class="text-sm font-medium text-white">{{ $todayShift->date->format('l, M d') }}</p>
                        </div>
                        @if($activeTimeEntry)
                            <span class="px-2 py-1 bg-green-500/20 text-green-400 text-xs font-medium rounded">Clocked In</span>
                        @else
                            <span class="px-2 py-1 bg-gray-700 text-gray-400 text-xs font-medium rounded">Not Started</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <div class="text-xl font-bold text-white">{{ $todayShift->start_time->format('g:i A') }} - {{ $todayShift->end_time->format('g:i A') }}</div>
                            <div class="text-sm text-gray-500 mt-1">{{ $todayShift->working_hours }}h {{ $todayShift->department?->name ? '• ' . $todayShift->department->name : '' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Clock In/Out Section -->
                <div class="p-4 bg-gray-800/50">
                    @if($activeTimeEntry)
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="text-center p-3 bg-gray-900 rounded-lg border border-gray-800">
                                <p class="text-xs text-gray-500 mb-1">Clocked In</p>
                                <p class="text-lg font-bold text-green-400">{{ $activeTimeEntry->clock_in_at->format('g:i A') }}</p>
                            </div>
                            <div class="text-center p-3 bg-gray-900 rounded-lg border border-gray-800">
                                <p class="text-xs text-gray-500 mb-1">Working</p>
                                <p class="text-lg font-bold text-white">{{ $activeTimeEntry->current_duration }}</p>
                            </div>
                        </div>
                        <a href="{{ route('time-clock.index') }}" class="flex items-center justify-center gap-2 py-3 bg-red-500 text-white rounded-lg font-medium w-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Clock Out
                        </a>
                    @else
                        <a href="{{ route('time-clock.index') }}" class="flex items-center justify-center gap-2 py-3 bg-brand-400 text-white rounded-lg font-medium w-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            Clock In
                        </a>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-gray-900 rounded-lg border border-gray-800 p-6 text-center">
                <svg class="w-10 h-10 text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-sm text-gray-500">No shift scheduled for today</p>
                <a href="{{ route('my-shifts.index') }}" class="text-sm text-brand-400 mt-2 inline-block">View your schedule</a>
            </div>
        @endif
    </div>

    <!-- This Week Summary -->
    <div class="mb-6">
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">This Week</h2>
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-gray-900 rounded-lg p-4 border border-gray-800 text-center">
                <p class="text-2xl font-bold text-brand-400">{{ $weekSummary['scheduled_hours'] }}h</p>
                <p class="text-xs text-gray-500 mt-1">Scheduled</p>
            </div>
            <div class="bg-gray-900 rounded-lg p-4 border border-gray-800 text-center">
                <p class="text-2xl font-bold text-white">{{ $weekSummary['worked_hours'] }}h</p>
                <p class="text-xs text-gray-500 mt-1">Worked</p>
            </div>
            <div class="bg-gray-900 rounded-lg p-4 border border-gray-800 text-center">
                <p class="text-2xl font-bold text-white">{{ $weekSummary['shifts_remaining'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Shifts Left</p>
            </div>
        </div>
    </div>

    <!-- Upcoming Shifts -->
    @if($upcomingShifts->isNotEmpty())
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Upcoming Shifts</h2>
                <a href="{{ route('my-shifts.index') }}" class="text-sm text-brand-400">View All</a>
            </div>
            <div class="space-y-2">
                @foreach($upcomingShifts->take(3) as $shift)
                    <div class="bg-gray-900 rounded-lg p-3 border border-gray-800 flex items-center gap-3">
                        <div class="w-12 h-12 bg-brand-900/50 rounded-lg flex flex-col items-center justify-center">
                            <span class="text-xs font-medium text-brand-300">{{ $shift->date->format('D') }}</span>
                            <span class="text-lg font-bold text-brand-400">{{ $shift->date->format('d') }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white">{{ $shift->start_time->format('g:i A') }} - {{ $shift->end_time->format('g:i A') }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $shift->department?->name }} {{ $shift->businessRole ? '• ' . $shift->businessRole->name : '' }}</p>
                        </div>
                        <span class="text-sm text-gray-500">{{ $shift->working_hours }}h</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Leave Balance -->
    @if($leaveBalances->isNotEmpty())
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Leave Balance</h2>
                <a href="{{ route('leave-requests.create') }}" class="text-sm text-brand-400">Request Leave</a>
            </div>
            <div class="space-y-2">
                @foreach($leaveBalances->take(2) as $balance)
                    <div class="bg-gray-900 rounded-lg p-3 border border-gray-800">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-green-500/10 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-white">{{ $balance->leaveType->name }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-white">{{ $balance->remaining_days }}</p>
                                <p class="text-xs text-gray-500">of {{ $balance->total_days }} days</p>
                            </div>
                        </div>
                        @php
                            $percentage = $balance->total_days > 0 ? ($balance->remaining_days / $balance->total_days) * 100 : 0;
                        @endphp
                        <div class="w-full bg-gray-800 rounded-full h-1.5">
                            <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Pending Requests -->
    @if($pendingLeave->isNotEmpty() || $pendingSwaps->isNotEmpty() || $incomingSwaps > 0)
        <div class="mb-6">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Pending Requests</h2>
            <div class="space-y-2">
                @foreach($pendingLeave as $leave)
                    <div class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-3">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-amber-500/20 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-amber-300">Leave Request</p>
                                <p class="text-xs text-amber-400/70">{{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d') }} ({{ $leave->leaveType->name }})</p>
                                <p class="text-xs text-amber-400/50 mt-1">Awaiting approval</p>
                            </div>
                        </div>
                    </div>
                @endforeach

                @foreach($pendingSwaps as $swap)
                    <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-3">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-blue-300">Swap Request</p>
                                <p class="text-xs text-blue-400/70">{{ $swap->requestingShift->date->format('D, M d') }} - {{ $swap->requestingShift->start_time->format('g:i A') }}</p>
                                <p class="text-xs text-blue-400/50 mt-1">Awaiting approval</p>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if($incomingSwaps > 0)
                    <a href="{{ route('my-swaps.index') }}" class="block bg-green-500/10 border border-green-500/30 rounded-lg p-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-green-500/20 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-green-300">{{ $incomingSwaps }} incoming swap {{ $incomingSwaps === 1 ? 'request' : 'requests' }}</p>
                                <p class="text-xs text-green-400/70">Tap to review</p>
                            </div>
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </a>
                @endif
            </div>
        </div>
    @endif

    <!-- Quick Actions -->
    <div>
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Quick Actions</h2>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('my-shifts.index') }}" class="bg-gray-900 rounded-lg border border-gray-800 p-4 flex items-center gap-3">
                <div class="w-10 h-10 bg-brand-900/50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <span class="text-sm text-white">My Shifts</span>
            </a>
            <a href="{{ route('my-swaps.index') }}" class="bg-gray-900 rounded-lg border border-gray-800 p-4 flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <span class="text-sm text-white">Swaps</span>
            </a>
        </div>
    </div>
</x-layouts.mobile>

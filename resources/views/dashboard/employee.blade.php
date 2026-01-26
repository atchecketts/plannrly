<x-layouts.mobile title="Dashboard" active="home">
    @php
        $primaryRole = $user->businessRoles->firstWhere('pivot.is_primary', true)
            ?? $user->businessRoles->first();
        $department = $primaryRole?->department;
    @endphp

    <!-- Header Section with Blue Gradient -->
    <div class="bg-gradient-to-br from-indigo-900 via-indigo-800 to-purple-900 px-4 pt-4 pb-6">
        <!-- User Greeting -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 bg-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                    {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                </div>
                <div>
                    <p class="text-lg font-semibold text-white">Hello, {{ $user->first_name }}!</p>
                    <p class="text-sm text-indigo-200">{{ $department?->name ?? 'No Department' }} &bull; {{ $primaryRole?->name ?? 'Employee' }}</p>
                </div>
            </div>
            <button class="relative p-2 text-white/80 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                @if(($incomingSwaps ?? 0) > 0)
                    <span class="absolute top-1 right-1 w-2 h-2 bg-orange-500 rounded-full"></span>
                @endif
            </button>
        </div>

        <!-- Today's Shift Card -->
        @if($todayShift)
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <p class="text-xs text-indigo-300 uppercase tracking-wider">Today's Shift</p>
                        <p class="text-sm text-white/80">{{ $todayShift->date->format('l, M j') }}</p>
                    </div>
                    @if($activeTimeEntry)
                        <span class="px-3 py-1 text-xs font-medium bg-green-500 text-white rounded-full">Active</span>
                    @else
                        <span class="px-3 py-1 text-xs font-medium bg-indigo-500/50 text-indigo-100 rounded-full">Scheduled</span>
                    @endif
                </div>
                <p class="text-3xl font-bold text-white mb-1">
                    {{ $todayShift->start_time->format('g:i A') }} - {{ $todayShift->end_time->format('g:i A') }}
                </p>
                <p class="text-sm text-indigo-200">
                    {{ $todayShift->working_hours }} hours
                    @if($todayShift->break_duration)
                        &bull; {{ $todayShift->break_duration }} min break
                    @endif
                </p>
            </div>

            <!-- Clock Status -->
            @if($activeTimeEntry)
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-white/10 backdrop-blur rounded-xl p-3 text-center">
                        <p class="text-xs text-indigo-200 mb-1">Clocked In</p>
                        <p class="text-xl font-bold text-green-400">{{ $activeTimeEntry->clock_in_at->format('g:i A') }}</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur rounded-xl p-3 text-center">
                        <p class="text-xs text-indigo-200 mb-1">Working</p>
                        <p class="text-xl font-bold text-white">{{ $activeTimeEntry->current_duration }}</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('time-clock.index') }}" class="py-3 bg-teal-500 hover:bg-teal-600 text-white font-medium rounded-xl flex items-center justify-center gap-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Start Break
                    </a>
                    <a href="{{ route('time-clock.index') }}" class="py-3 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-xl flex items-center justify-center gap-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Clock Out
                    </a>
                </div>
            @else
                <!-- Clock In Button -->
                <a href="{{ route('time-clock.index') }}" class="w-full py-4 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-xl flex items-center justify-center gap-2 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Clock In
                </a>
            @endif
        @else
            <!-- No Shift Today -->
            <div class="bg-white/10 backdrop-blur rounded-xl p-4 text-center">
                <svg class="w-10 h-10 text-indigo-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-white font-medium">No shift scheduled today</p>
                <p class="text-sm text-indigo-200">Enjoy your day off!</p>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <div class="px-4 py-4">
        <!-- This Week Stats -->
        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-3">This Week</p>
        <div class="grid grid-cols-3 gap-3 mb-6">
            <div class="bg-[#1a1a2e] rounded-xl p-3 text-center">
                <p class="text-2xl font-bold text-indigo-400">{{ $weekSummary['scheduled_hours'] }}h</p>
                <p class="text-xs text-gray-500">Scheduled</p>
            </div>
            <div class="bg-[#1a1a2e] rounded-xl p-3 text-center">
                <p class="text-2xl font-bold text-white">{{ $weekSummary['worked_hours'] }}h</p>
                <p class="text-xs text-gray-500">Worked</p>
            </div>
            <div class="bg-[#1a1a2e] rounded-xl p-3 text-center">
                <p class="text-2xl font-bold text-white">{{ $weekSummary['shifts_remaining'] }}</p>
                <p class="text-xs text-gray-500">Shifts Left</p>
            </div>
        </div>

        <!-- Upcoming Shifts -->
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Upcoming Shifts</p>
            <a href="{{ route('my-shifts.index') }}" class="text-sm text-indigo-400 font-medium">View All</a>
        </div>

        @if($upcomingShifts->isEmpty())
            <div class="bg-[#1a1a2e] rounded-xl p-6 text-center mb-6">
                <svg class="w-8 h-8 text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-sm text-gray-500">No upcoming shifts</p>
            </div>
        @else
            <div class="space-y-2 mb-6">
                @foreach($upcomingShifts->take(4) as $shift)
                    <div class="bg-[#1a1a2e] rounded-xl p-3 flex items-center gap-3">
                        <div class="w-12 h-14 bg-indigo-600 rounded-lg flex flex-col items-center justify-center shrink-0">
                            <span class="text-[10px] text-indigo-200 uppercase">{{ $shift->date->format('D') }}</span>
                            <span class="text-lg font-bold text-white">{{ $shift->date->format('j') }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white">
                                {{ $shift->start_time->format('g:i A') }} - {{ $shift->end_time->format('g:i A') }}
                            </p>
                            <p class="text-xs text-gray-500 truncate">
                                {{ $shift->department?->name ?? 'No Department' }}
                                @if($shift->businessRole)
                                    &bull; {{ $shift->businessRole->name }}
                                @endif
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm text-gray-400">{{ $shift->working_hours }}h</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Pending Requests -->
        @if($pendingLeave->isNotEmpty() || $pendingSwaps->isNotEmpty() || ($incomingSwaps ?? 0) > 0)
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-3">Pending Requests</p>
            <div class="space-y-2 mb-6">
                @foreach($pendingLeave as $leave)
                    <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-3">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-amber-500/20 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-amber-300">Leave Request</p>
                                <p class="text-xs text-amber-400/70">{{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d') }} ({{ $leave->leaveType->name }})</p>
                            </div>
                        </div>
                    </div>
                @endforeach

                @foreach($pendingSwaps as $swap)
                    <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-3">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-blue-300">Swap Request</p>
                                <p class="text-xs text-blue-400/70">{{ $swap->requestingShift->date->format('D, M d') }} - {{ $swap->requestingShift->start_time->format('g:i A') }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if(($incomingSwaps ?? 0) > 0)
                    <a href="{{ route('my-swaps.index') }}" class="block bg-green-500/10 border border-green-500/30 rounded-xl p-3">
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
        @endif

        <!-- Leave Balance -->
        @if($leaveBalances->isNotEmpty())
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Leave Balance</p>
                <a href="{{ route('my-leave.create') }}" class="text-sm text-indigo-400 font-medium">Request</a>
            </div>
            <div class="space-y-2">
                @foreach($leaveBalances->take(2) as $balance)
                    <div class="bg-[#1a1a2e] rounded-xl p-3">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-medium text-white">{{ $balance->leaveType->name }}</p>
                            <p class="text-sm font-bold text-white">{{ $balance->remaining_days }} <span class="text-gray-500 font-normal">/ {{ $balance->total_days }} days</span></p>
                        </div>
                        @php
                            $percentage = $balance->total_days > 0 ? ($balance->remaining_days / $balance->total_days) * 100 : 0;
                        @endphp
                        <div class="w-full bg-gray-800 rounded-full h-1.5">
                            <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.mobile>

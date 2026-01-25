<x-layouts.mobile title="Dashboard" active="home">
    <div class="px-4 -mt-4 space-y-6">
        <!-- Today's Shift Card -->
        @if($todayShift)
            <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
                <div class="p-4 border-b border-gray-800">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-sm text-gray-500">Today's Shift</p>
                            <p class="font-semibold text-white">{{ now()->format('l, M d') }}</p>
                        </div>
                        @if($activeTimeEntry)
                            <span class="px-3 py-1 bg-green-500/20 text-green-400 text-sm font-medium rounded-full">
                                {{ $activeTimeEntry->status->label() }}
                            </span>
                        @else
                            <span class="px-3 py-1 bg-gray-700 text-gray-300 text-sm font-medium rounded-full">
                                Upcoming
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <div class="text-2xl font-bold text-white">
                                {{ $todayShift->start_time->format('g:i A') }} - {{ $todayShift->end_time->format('g:i A') }}
                            </div>
                            <div class="text-sm text-gray-500 mt-1">
                                {{ number_format($todayShift->working_hours, 1) }} hours
                                @if($todayShift->break_duration_minutes)
                                    &bull; {{ $todayShift->break_duration_minutes }} min break
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clock In/Out Section -->
                <div class="p-4 bg-gray-800/50">
                    @if($activeTimeEntry)
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="text-center p-3 bg-gray-900 rounded-xl border border-gray-800">
                                <p class="text-xs text-gray-500 mb-1">Clocked In</p>
                                <p class="text-lg font-bold text-green-400">{{ $activeTimeEntry->clock_in_at->format('g:i A') }}</p>
                            </div>
                            <div class="text-center p-3 bg-gray-900 rounded-xl border border-gray-800">
                                <p class="text-xs text-gray-500 mb-1">Working</p>
                                <p class="text-lg font-bold text-white" id="time-worked">
                                    {{ gmdate('G\h i\m', $activeTimeEntry->clock_in_at->diffInSeconds(now())) }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            @if($activeTimeEntry->status->value === 'clocked_in')
                                <form action="{{ route('time-clock.start-break') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 bg-amber-500 text-white rounded-xl font-medium hover:bg-amber-400 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Start Break
                                    </button>
                                </form>
                            @elseif($activeTimeEntry->status->value === 'on_break')
                                <form action="{{ route('time-clock.end-break') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 bg-green-500 text-white rounded-xl font-medium hover:bg-green-400 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        End Break
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('time-clock.clock-out') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 bg-red-500 text-white rounded-xl font-medium hover:bg-red-400 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Clock Out
                                </button>
                            </form>
                        </div>
                    @else
                        <form action="{{ route('time-clock.clock-in') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center gap-3 py-4 bg-green-500 text-white rounded-xl font-semibold text-lg hover:bg-green-400 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                Clock In
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @else
            <!-- No Shift Today -->
            <div class="bg-gray-900 rounded-2xl border border-gray-800 p-8 text-center">
                <div class="w-16 h-16 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">No Shift Today</h3>
                <p class="text-sm text-gray-500">Enjoy your day off!</p>
            </div>
        @endif

        <!-- This Week Summary -->
        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">This Week</h2>
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-gray-900 rounded-xl p-4 border border-gray-800 text-center">
                    <p class="text-2xl font-bold text-brand-400">{{ $weekSummary['scheduled_hours'] }}h</p>
                    <p class="text-xs text-gray-500 mt-1">Scheduled</p>
                </div>
                <div class="bg-gray-900 rounded-xl p-4 border border-gray-800 text-center">
                    <p class="text-2xl font-bold text-white">{{ $weekSummary['worked_hours'] }}h</p>
                    <p class="text-xs text-gray-500 mt-1">Worked</p>
                </div>
                <div class="bg-gray-900 rounded-xl p-4 border border-gray-800 text-center">
                    <p class="text-2xl font-bold text-white">{{ $weekSummary['shifts_remaining'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Shifts Left</p>
                </div>
            </div>
        </div>

        <!-- Upcoming Shifts -->
        @if($upcomingShifts->isNotEmpty())
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Upcoming Shifts</h2>
                    <a href="{{ route('my-shifts.index') }}" class="text-sm text-brand-400 font-medium">View All</a>
                </div>
                <div class="space-y-3">
                    @foreach($upcomingShifts as $shift)
                        <div class="bg-gray-900 rounded-xl p-4 border border-gray-800 flex items-center gap-4">
                            <div class="w-12 h-12 bg-brand-600/20 rounded-xl flex flex-col items-center justify-center border border-brand-700/50">
                                <span class="text-xs font-medium text-brand-300">{{ $shift->date->format('D') }}</span>
                                <span class="text-lg font-bold text-brand-400">{{ $shift->date->format('d') }}</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-white">
                                    {{ $shift->start_time->format('g:i A') }} - {{ $shift->end_time->format('g:i A') }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    @if($shift->department)
                                        {{ $shift->department->name }}
                                    @endif
                                    @if($shift->businessRole)
                                        @if($shift->department) &bull; @endif
                                        {{ $shift->businessRole->name }}
                                    @endif
                                </p>
                            </div>
                            <span class="text-sm text-gray-500">{{ number_format($shift->working_hours, 1) }}h</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Leave Balance -->
        @if($leaveBalances->isNotEmpty())
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Leave Balance</h2>
                    <a href="{{ route('leave-requests.create') }}" class="text-sm text-brand-400 font-medium">Request Leave</a>
                </div>
                @foreach($leaveBalances->take(2) as $balance)
                    <div class="bg-gray-900 rounded-xl p-4 border border-gray-800 mb-3">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-white">{{ $balance->leaveType->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $balance->remaining_days }} days remaining</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-white">{{ $balance->remaining_days }}</p>
                                <p class="text-xs text-gray-500">of {{ $balance->total_available }} days</p>
                            </div>
                        </div>
                        @php
                            $percentage = $balance->total_available > 0
                                ? ($balance->remaining_days / $balance->total_available) * 100
                                : 0;
                        @endphp
                        <div class="w-full bg-gray-800 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Pending Requests -->
        @if($pendingLeave->isNotEmpty() || $pendingSwaps->isNotEmpty() || $incomingSwaps > 0)
            <div class="mb-24">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Pending Requests</h2>
                <div class="space-y-3">
                    @foreach($pendingLeave as $leave)
                        <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-amber-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-amber-300">Leave Request</p>
                                    <p class="text-sm text-amber-400/70">
                                        {{ $leave->start_date->format('M d') }}
                                        @if(!$leave->start_date->eq($leave->end_date))
                                            - {{ $leave->end_date->format('M d') }}
                                        @endif
                                        ({{ $leave->leaveType->name }})
                                    </p>
                                    <p class="text-xs text-amber-400/50 mt-1">Awaiting approval</p>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @foreach($pendingSwaps as $swap)
                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-blue-300">Swap Request</p>
                                    <p class="text-sm text-blue-400/70">
                                        {{ $swap->requestingShift->date->format('M d') }} -
                                        {{ $swap->requestingShift->start_time->format('g:i A') }}
                                    </p>
                                    <p class="text-xs text-blue-400/50 mt-1">Awaiting response</p>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if($incomingSwaps > 0)
                        <a href="{{ route('my-swaps.index') }}" class="block bg-green-500/10 border border-green-500/30 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-green-300">{{ $incomingSwaps }} Incoming Swap Request(s)</p>
                                    <p class="text-xs text-green-400/50 mt-1">Tap to review and respond</p>
                                </div>
                                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </a>
                    @endif
                </div>
            </div>
        @else
            <div class="h-24"></div>
        @endif
    </div>

    @if($activeTimeEntry)
    <script>
        // Update working time every second
        setInterval(function() {
            const clockInTime = new Date('{{ $activeTimeEntry->clock_in_at->toISOString() }}');
            const now = new Date();
            const diff = Math.floor((now - clockInTime) / 1000);
            const hours = Math.floor(diff / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            document.getElementById('time-worked').textContent = hours + 'h ' + minutes + 'm';
        }, 1000);
    </script>
    @endif
</x-layouts.mobile>

<x-layouts.admin-mobile title="Dashboard" active="home">
    <div class="px-4 -mt-4 space-y-6">
        <!-- Stats Grid -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-gray-900 rounded-xl p-4 border border-gray-800">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-white">{{ $stats['on_duty_today'] }}</p>
                <p class="text-xs text-gray-500">On Duty Today</p>
            </div>

            <div class="bg-gray-900 rounded-xl p-4 border border-gray-800">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-amber-500/10 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-white">{{ $stats['on_leave_today'] }}</p>
                <p class="text-xs text-gray-500">On Leave</p>
            </div>

            <div class="bg-gray-900 rounded-xl p-4 border border-gray-800">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-brand-500/10 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-brand-400">{{ $stats['hours_this_week'] }}h</p>
                <p class="text-xs text-gray-500">Hours This Week</p>
            </div>

            <div class="bg-gray-900 rounded-xl p-4 border border-gray-800">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-red-500/10 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold {{ $stats['unassigned_shifts'] > 0 ? 'text-red-500' : 'text-white' }}">{{ $stats['unassigned_shifts'] }}</p>
                <p class="text-xs text-gray-500">Unassigned Shifts</p>
            </div>
        </div>

        <!-- Pending Leave Requests -->
        @if($pendingLeave->isNotEmpty())
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Pending Leave</h2>
                    <a href="{{ route('leave-requests.index') }}" class="text-sm text-brand-400 font-medium">View All</a>
                </div>
                <div class="space-y-3">
                    @foreach($pendingLeave as $leave)
                        <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-amber-500/20 rounded-full flex items-center justify-center flex-shrink-0 text-amber-400 font-medium text-sm">
                                    {{ substr($leave->user->first_name, 0, 1) }}{{ substr($leave->user->last_name, 0, 1) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-amber-300">{{ $leave->user->full_name }}</p>
                                    <p class="text-sm text-amber-400/70">
                                        {{ $leave->leaveType->name }} &bull;
                                        {{ $leave->start_date->format('M d') }}@if(!$leave->start_date->eq($leave->end_date))-{{ $leave->end_date->format('d') }}@endif
                                    </p>
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
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Pending Shift Swaps -->
        @if($pendingSwaps->isNotEmpty())
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Pending Swaps</h2>
                    <a href="{{ route('shift-swaps.index') }}" class="text-sm text-brand-400 font-medium">View All</a>
                </div>
                <div class="space-y-3">
                    @foreach($pendingSwaps as $swap)
                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <div class="flex -space-x-2 flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500/30 rounded-full flex items-center justify-center text-blue-400 font-medium text-xs ring-2 ring-gray-950">
                                        {{ substr($swap->requestingShift->user->first_name, 0, 1) }}{{ substr($swap->requestingShift->user->last_name, 0, 1) }}
                                    </div>
                                    @if($swap->targetShift?->user)
                                        <div class="w-8 h-8 bg-blue-500/30 rounded-full flex items-center justify-center text-blue-300 font-medium text-xs ring-2 ring-gray-950">
                                            {{ substr($swap->targetShift->user->first_name, 0, 1) }}{{ substr($swap->targetShift->user->last_name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-blue-300 text-sm">
                                        {{ $swap->requestingShift->user->full_name }}
                                        @if($swap->targetShift?->user)
                                            <span class="text-blue-400/50">&#8596;</span> {{ $swap->targetShift->user->full_name }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-blue-400/70 mt-0.5">
                                        {{ $swap->requestingShift->date->format('D, M d') }} &bull;
                                        {{ $swap->requestingShift->start_time->format('g:i A') }}
                                    </p>
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
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Today's Shifts -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Today's Shifts</h2>
                <a href="{{ route('schedule.day') }}" class="text-sm text-brand-400 font-medium">View All</a>
            </div>

            @if($todayShifts->isEmpty())
                <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 text-center">
                    <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500">No shifts scheduled for today</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($todayShifts->take(6) as $shift)
                        <div class="bg-gray-900 rounded-xl border {{ $shift->user_id ? 'border-gray-800' : 'border-red-500/30 border-dashed' }} p-4">
                            <div class="flex items-center gap-3">
                                @if($shift->user)
                                    <div class="w-10 h-10 bg-brand-900/50 rounded-full flex items-center justify-center text-brand-300 font-medium text-sm">
                                        {{ substr($shift->user->first_name, 0, 1) }}{{ substr($shift->user->last_name, 0, 1) }}
                                    </div>
                                @else
                                    <div class="w-10 h-10 bg-red-500/20 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    @if($shift->user)
                                        <p class="font-medium text-white text-sm">{{ $shift->user->full_name }}</p>
                                    @else
                                        <p class="font-medium text-red-400 text-sm">Unassigned</p>
                                    @endif
                                    <p class="text-xs text-gray-500">
                                        {{ $shift->department?->name }}
                                        @if($shift->businessRole)
                                            &bull; {{ $shift->businessRole->name }}
                                        @endif
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-white">{{ $shift->start_time->format('g:i A') }}</p>
                                    <p class="text-xs text-gray-500">{{ $shift->end_time->format('g:i A') }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @if($todayShifts->count() > 6)
                        <a href="{{ route('schedule.day') }}" class="block text-center py-3 text-sm text-brand-400 font-medium">
                            + {{ $todayShifts->count() - 6 }} more shifts
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Quick Actions</h2>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('schedule.index') }}" class="bg-gray-900 rounded-xl border border-gray-800 p-4 flex flex-col items-center gap-2 hover:bg-gray-800/50 transition-colors">
                    <div class="w-12 h-12 bg-brand-500/10 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-white">Schedule</span>
                </a>
                <a href="{{ route('users.create') }}" class="bg-gray-900 rounded-xl border border-gray-800 p-4 flex flex-col items-center gap-2 hover:bg-gray-800/50 transition-colors">
                    <div class="w-12 h-12 bg-green-500/10 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-white">Add Employee</span>
                </a>
                <a href="{{ route('shift-swaps.index') }}" class="bg-gray-900 rounded-xl border border-gray-800 p-4 flex flex-col items-center gap-2 hover:bg-gray-800/50 transition-colors">
                    <div class="w-12 h-12 bg-blue-500/10 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-white">Swaps</span>
                </a>
                <a href="{{ route('departments.index') }}" class="bg-gray-900 rounded-xl border border-gray-800 p-4 flex flex-col items-center gap-2 hover:bg-gray-800/50 transition-colors">
                    <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-white">Departments</span>
                </a>
            </div>
        </div>

        <div class="h-6"></div>
    </div>
</x-layouts.admin-mobile>

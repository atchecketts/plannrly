<x-layouts.app title="Dashboard">
    <x-slot:header>
        <div>
            <h1 class="text-2xl font-bold text-white">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-0.5">Welcome back, {{ auth()->user()->first_name }}. Here's what's happening today.</p>
        </div>
    </x-slot:header>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- On Duty Card -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">On Duty Today</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ $stats['on_duty_today'] }}</p>
                    <p class="text-sm text-gray-500 mt-1">of {{ $stats['active_employees'] }} staff</p>
                </div>
                <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- On Leave Card -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">On Leave</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ $stats['on_leave_today'] }}</p>
                    <p class="text-sm text-gray-500 mt-1">employees</p>
                </div>
                <div class="w-12 h-12 bg-amber-500/10 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Hours This Week -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Hours This Week</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ $stats['hours_this_week'] }}</p>
                    <p class="text-sm text-gray-500 mt-1">scheduled</p>
                </div>
                <div class="w-12 h-12 bg-brand-500/10 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Unassigned Shifts -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Unassigned Shifts</p>
                    <p class="text-3xl font-bold {{ $stats['unassigned_shifts'] > 0 ? 'text-red-500' : 'text-white' }} mt-1">{{ $stats['unassigned_shifts'] }}</p>
                    <p class="text-sm text-gray-500 mt-1">this week</p>
                </div>
                <div class="w-12 h-12 bg-red-500/10 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Cards & Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Pending Leave Requests -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                <h3 class="font-semibold text-white">Pending Leave Requests</h3>
                @if($stats['pending_leave_requests'] > 0)
                    <span class="bg-amber-500/20 text-amber-400 text-xs font-medium px-2.5 py-1 rounded-full">{{ $stats['pending_leave_requests'] }} pending</span>
                @endif
            </div>
            <div class="divide-y divide-gray-800">
                @forelse($pendingLeave as $leave)
                    <div class="px-6 py-4 hover:bg-gray-800/50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-purple-500/20 rounded-full flex items-center justify-center text-purple-400 font-medium text-sm">
                                {{ substr($leave->user->first_name, 0, 1) }}{{ substr($leave->user->last_name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-white">{{ $leave->user->full_name }}</p>
                                <p class="text-sm text-gray-500">{{ $leave->leaveType->name }} &bull; {{ $leave->start_date->format('M d') }}-{{ $leave->end_date->format('d') }}</p>
                            </div>
                            <div class="flex gap-2">
                                <form action="{{ route('leave-requests.approve', $leave) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-2 text-green-500 hover:bg-green-500/10 rounded-lg transition-colors" title="Approve">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </form>
                                <form action="{{ route('leave-requests.reject', $leave) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors" title="Reject">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center">
                        <p class="text-sm text-gray-500">No pending leave requests</p>
                    </div>
                @endforelse
            </div>
            @if($stats['pending_leave_requests'] > 0)
                <div class="px-6 py-3 bg-gray-800/50 border-t border-gray-800">
                    <a href="{{ route('leave-requests.index') }}" class="text-sm font-medium text-brand-400 hover:text-brand-300">View all requests &rarr;</a>
                </div>
            @endif
        </div>

        <!-- Shift Swap Requests -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                <h3 class="font-semibold text-white">Shift Swap Requests</h3>
                @if($stats['pending_shift_swaps'] > 0)
                    <span class="bg-amber-500/20 text-amber-400 text-xs font-medium px-2.5 py-1 rounded-full">{{ $stats['pending_shift_swaps'] }} pending</span>
                @endif
            </div>
            <div class="divide-y divide-gray-800">
                @forelse($pendingSwaps as $swap)
                    <div class="px-6 py-4 hover:bg-gray-800/50 transition-colors">
                        <div class="flex items-start gap-4">
                            <div class="flex -space-x-2">
                                <div class="w-8 h-8 bg-purple-500/20 rounded-full flex items-center justify-center text-purple-400 font-medium text-xs ring-2 ring-gray-900">
                                    {{ substr($swap->requestingShift->user->first_name, 0, 1) }}{{ substr($swap->requestingShift->user->last_name, 0, 1) }}
                                </div>
                                @if($swap->targetShift?->user)
                                    <div class="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center text-blue-400 font-medium text-xs ring-2 ring-gray-900">
                                        {{ substr($swap->targetShift->user->first_name, 0, 1) }}{{ substr($swap->targetShift->user->last_name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-white text-sm">
                                    {{ $swap->requestingShift->user->full_name }}
                                    @if($swap->targetShift?->user)
                                        &#8596; {{ $swap->targetShift->user->full_name }}
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $swap->requestingShift->date->format('D') }} {{ $swap->requestingShift->start_time->format('g:i A') }}</p>
                                <p class="text-xs text-gray-600 mt-0.5">{{ $swap->requestingShift->department->name }} &bull; {{ $swap->requestingShift->businessRole->name }}</p>
                            </div>
                            <div class="flex gap-1">
                                <form action="{{ route('shift-swaps.approve', $swap) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-1.5 text-green-500 hover:bg-green-500/10 rounded-lg transition-colors" title="Approve">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </form>
                                <form action="{{ route('shift-swaps.reject', $swap) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-1.5 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors" title="Reject">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center">
                        <p class="text-sm text-gray-500">No pending swap requests</p>
                    </div>
                @endforelse
            </div>
            @if($stats['pending_shift_swaps'] > 0)
                <div class="px-6 py-3 bg-gray-800/50 border-t border-gray-800">
                    <a href="{{ route('shift-swaps.index') }}" class="text-sm font-medium text-brand-400 hover:text-brand-300">View all swaps &rarr;</a>
                </div>
            @endif
        </div>

        <!-- Quick Stats / Alerts -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">Quick Actions</h3>
            </div>
            <div class="divide-y divide-gray-800">
                <a href="{{ route('schedule.index') }}" class="px-6 py-4 flex items-center gap-3 hover:bg-gray-800/50 transition-colors">
                    <div class="w-10 h-10 bg-brand-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-white">View Schedule</p>
                        <p class="text-xs text-gray-500">Manage weekly shifts</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                <a href="{{ route('users.index') }}" class="px-6 py-4 flex items-center gap-3 hover:bg-gray-800/50 transition-colors">
                    <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-white">Add Employee</p>
                        <p class="text-xs text-gray-500">Invite new team member</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                <a href="{{ route('leave-requests.index') }}" class="px-6 py-4 flex items-center gap-3 hover:bg-gray-800/50 transition-colors">
                    <div class="w-10 h-10 bg-amber-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-white">Leave Management</p>
                        <p class="text-xs text-gray-500">Review and approve leave</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Today's Schedule Preview -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-white">Today's Schedule</h3>
                <p class="text-sm text-gray-500">{{ now()->format('l, F j, Y') }}</p>
            </div>
            <a href="{{ route('schedule.day') }}" class="flex items-center gap-2 text-sm font-medium text-brand-400 hover:text-brand-300">
                View full schedule
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
        <div class="p-6">
            @if($todayShifts->isEmpty())
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-gray-500">No shifts scheduled for today</p>
                    <a href="{{ route('schedule.index') }}" class="inline-block mt-4 text-sm font-medium text-brand-400 hover:text-brand-300">
                        Create a shift &rarr;
                    </a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($shiftsByDepartment as $departmentId => $shifts)
                        @php $department = $shifts->first()->department; @endphp
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-3 h-3 rounded-full" style="background-color: {{ $department->color ?? '#6366f1' }}"></div>
                                <span class="text-sm font-medium text-gray-300">{{ $department->name }}</span>
                                <span class="text-xs text-gray-600">{{ $shifts->count() }} shifts</span>
                            </div>
                            <div class="ml-6 space-y-2">
                                @foreach($shifts->take(3) as $shift)
                                    <div class="flex items-center gap-4 py-2 px-3 rounded-lg {{ $shift->user_id ? 'bg-gray-800/50' : 'bg-red-500/10 border border-dashed border-red-500/30' }}">
                                        @if($shift->user)
                                            <div class="w-8 h-8 bg-brand-900/50 rounded-full flex items-center justify-center text-brand-300 font-medium text-xs">
                                                {{ substr($shift->user->first_name, 0, 1) }}{{ substr($shift->user->last_name, 0, 1) }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-white truncate">{{ $shift->user->full_name }}</p>
                                                <p class="text-xs text-gray-500">{{ $shift->businessRole->name }}</p>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 bg-red-500/20 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-red-400">Unassigned</p>
                                                <p class="text-xs text-gray-500">{{ $shift->businessRole->name }}</p>
                                            </div>
                                        @endif
                                        <div class="text-right">
                                            <p class="text-sm font-medium text-white">{{ $shift->start_time->format('g:i A') }}</p>
                                            <p class="text-xs text-gray-500">{{ $shift->end_time->format('g:i A') }}</p>
                                        </div>
                                    </div>
                                @endforeach
                                @if($shifts->count() > 3)
                                    <p class="text-xs text-gray-600 pl-3">+ {{ $shifts->count() - 3 }} more shifts</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Legend -->
                <div class="mt-6 pt-6 border-t border-gray-800 flex items-center gap-6 text-xs">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-brand-600 rounded"></div>
                        <span class="text-gray-400">Assigned</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-red-500/30 rounded border border-dashed border-red-500"></div>
                        <span class="text-gray-400">Unassigned</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>

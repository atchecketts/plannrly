<x-layouts.app title="My Dashboard">
    <x-slot:header>
        <div>
            <h1 class="text-2xl font-bold text-white">My Dashboard</h1>
            <p class="text-sm text-gray-500 mt-0.5">Welcome back, {{ auth()->user()->first_name }}. Here's your schedule overview.</p>
        </div>
    </x-slot:header>

    @php
        $clockInEnabled = \App\Models\TenantSettings::where('tenant_id', auth()->user()->tenant_id)->first()?->enable_clock_in_out ?? false;
    @endphp
    @if($clockInEnabled)
        <x-clock-widget />
    @endif

    <!-- Next Shift Countdown -->
    @if($nextShift)
        <div class="bg-gradient-to-r from-brand-900/50 to-brand-800/30 rounded-xl border border-brand-700/50 p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-brand-300">Your Next Shift</p>
                    <h2 class="text-2xl font-bold text-white mt-1">
                        {{ $nextShift->date->format('l, M d') }} at {{ $nextShift->start_time->format('g:i A') }}
                    </h2>
                    <div class="flex items-center gap-3 mt-2 text-sm text-gray-400">
                        @if($nextShift->businessRole)
                            <span class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $nextShift->businessRole->color }}"></span>
                                {{ $nextShift->businessRole->name }}
                            </span>
                        @endif
                        @if($nextShift->department)
                            <span>&bull;</span>
                            <span>{{ $nextShift->department->name }}</span>
                        @endif
                        @if($nextShift->location)
                            <span>&bull;</span>
                            <span>{{ $nextShift->location->name }}</span>
                        @endif
                    </div>
                </div>
                <div class="text-left md:text-right" x-data="countdown('{{ $nextShift->date->format('Y-m-d') }}T{{ $nextShift->start_time->format('H:i:s') }}')" x-init="init()">
                    <p class="text-sm text-brand-300">Starts in</p>
                    <p class="text-3xl font-bold text-white mt-1" x-text="timeDisplay"></p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 mb-8">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gray-800 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-white font-medium">No Upcoming Shifts</p>
                    <p class="text-sm text-gray-500">You don't have any shifts scheduled.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Missed Shifts Alert -->
    @if(($stats['missed_shifts'] ?? 0) > 0)
        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-red-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-medium text-red-400">{{ $stats['missed_shifts'] }} Missed {{ Str::plural('Shift', $stats['missed_shifts']) }} This Week</p>
                    <p class="text-sm text-red-300/70">You did not clock in for scheduled shifts. Please speak with your manager.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <p class="text-sm font-medium text-gray-500">Upcoming Shifts</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['upcoming_shifts'] }}</p>
            <p class="text-xs text-gray-600 mt-1">next 7 days</p>
        </div>
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <p class="text-sm font-medium text-gray-500">Hours This Week</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['hours_this_week'] }}</p>
            <p class="text-xs text-gray-600 mt-1">scheduled</p>
        </div>
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <p class="text-sm font-medium text-gray-500">Leave Requests</p>
            <p class="text-2xl font-bold {{ $stats['pending_leave'] > 0 ? 'text-amber-400' : 'text-white' }} mt-1">{{ $stats['pending_leave'] }}</p>
            <p class="text-xs text-gray-600 mt-1">pending</p>
        </div>
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <p class="text-sm font-medium text-gray-500">Swap Requests</p>
            <p class="text-2xl font-bold {{ $stats['pending_swaps'] > 0 ? 'text-amber-400' : 'text-white' }} mt-1">{{ $stats['pending_swaps'] }}</p>
            <p class="text-xs text-gray-600 mt-1">active</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Upcoming Shifts -->
        <div class="lg:col-span-2 bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                <h3 class="font-semibold text-white">My Upcoming Shifts</h3>
                <a href="{{ route('schedule.index') }}" class="text-sm text-brand-400 hover:text-brand-300">View full schedule</a>
            </div>
            <div class="divide-y divide-gray-800">
                @forelse($upcomingShifts as $shift)
                    <div class="px-6 py-4 hover:bg-gray-800/50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="text-center min-w-[60px]">
                                <p class="text-xs text-gray-500 uppercase">{{ $shift->date->format('D') }}</p>
                                <p class="text-2xl font-bold text-white">{{ $shift->date->format('d') }}</p>
                                <p class="text-xs text-gray-500">{{ $shift->date->format('M') }}</p>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    @if($shift->businessRole)
                                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $shift->businessRole->color }}"></span>
                                        <span class="text-sm font-medium text-white">{{ $shift->businessRole->name }}</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-400 mt-0.5">
                                    {{ $shift->start_time->format('g:i A') }} - {{ $shift->end_time->format('g:i A') }}
                                    <span class="text-gray-600">({{ $shift->working_hours }}h)</span>
                                </p>
                                @if($shift->department || $shift->location)
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $shift->department?->name }}{{ $shift->location ? ' @ ' . $shift->location->name : '' }}
                                    </p>
                                @endif
                            </div>
                            @if($shift->date->isToday())
                                <span class="bg-green-500/20 text-green-400 text-xs font-medium px-2.5 py-1 rounded-full">Today</span>
                            @elseif($shift->date->isTomorrow())
                                <span class="bg-brand-500/20 text-brand-400 text-xs font-medium px-2.5 py-1 rounded-full">Tomorrow</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <svg class="w-12 h-12 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-gray-500">No shifts scheduled for the next 7 days</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="font-semibold text-white">Quick Actions</h3>
                </div>
                <div class="divide-y divide-gray-800">
                    <a href="{{ route('leave-requests.create') }}" class="px-6 py-4 flex items-center gap-3 hover:bg-gray-800/50 transition-colors">
                        <div class="w-10 h-10 bg-amber-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-white">Request Time Off</p>
                            <p class="text-xs text-gray-500">Submit a leave request</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    <a href="{{ route('schedule.index') }}" class="px-6 py-4 flex items-center gap-3 hover:bg-gray-800/50 transition-colors">
                        <div class="w-10 h-10 bg-brand-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-white">View Full Schedule</p>
                            <p class="text-xs text-gray-500">See team schedule</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    <a href="{{ route('shift-swaps.index') }}" class="px-6 py-4 flex items-center gap-3 hover:bg-gray-800/50 transition-colors">
                        <div class="w-10 h-10 bg-purple-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-white">Shift Swaps</p>
                            <p class="text-xs text-gray-500">View swap requests</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- My Leave Requests -->
            <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                    <h3 class="font-semibold text-white">My Leave Requests</h3>
                    @if($myLeaveRequests->isNotEmpty())
                        <a href="{{ route('leave-requests.index') }}" class="text-xs text-brand-400 hover:text-brand-300">View all</a>
                    @endif
                </div>
                <div class="divide-y divide-gray-800">
                    @forelse($myLeaveRequests as $leave)
                        <div class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $leave->leaveType->color }}"></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-white">{{ $leave->leaveType->name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d') }}
                                        ({{ $leave->total_days }} {{ Str::plural('day', $leave->total_days) }})
                                    </p>
                                </div>
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                    {{ $leave->status->color() === 'gray' ? 'bg-gray-500/10 text-gray-400 ring-gray-500/20' : '' }}
                                    {{ $leave->status->color() === 'yellow' ? 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/20' : '' }}
                                    {{ $leave->status->color() === 'green' ? 'bg-green-500/10 text-green-400 ring-green-500/20' : '' }}
                                    {{ $leave->status->color() === 'red' ? 'bg-red-500/10 text-red-400 ring-red-500/20' : '' }}">
                                    {{ $leave->status->label() }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center">
                            <p class="text-sm text-gray-500">No pending leave requests</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- My Swap Requests -->
            <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                    <h3 class="font-semibold text-white">My Swap Requests</h3>
                    @if($mySwapRequests->isNotEmpty())
                        <a href="{{ route('shift-swaps.index') }}" class="text-xs text-brand-400 hover:text-brand-300">View all</a>
                    @endif
                </div>
                <div class="divide-y divide-gray-800">
                    @forelse($mySwapRequests as $swap)
                        <div class="px-6 py-4">
                            <div class="flex items-start gap-3">
                                <div class="flex -space-x-1.5 flex-shrink-0">
                                    <div class="w-7 h-7 bg-purple-500/20 rounded-full flex items-center justify-center text-purple-400 font-medium text-xs ring-2 ring-gray-900">
                                        {{ substr($swap->requestingUser->first_name, 0, 1) }}
                                    </div>
                                    <div class="w-7 h-7 bg-blue-500/20 rounded-full flex items-center justify-center text-blue-400 font-medium text-xs ring-2 ring-gray-900">
                                        {{ substr($swap->targetUser->first_name, 0, 1) }}
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    @if($swap->requesting_user_id === auth()->id())
                                        <p class="text-sm text-white">
                                            <span class="text-gray-400">You</span> → {{ $swap->targetUser->first_name }}
                                        </p>
                                        <p class="text-xs text-gray-500">{{ $swap->requestingShift->date->format('M d') }} {{ $swap->requestingShift->start_time->format('g:i A') }}</p>
                                    @else
                                        <p class="text-sm text-white">
                                            {{ $swap->requestingUser->first_name }} → <span class="text-gray-400">You</span>
                                        </p>
                                        <p class="text-xs text-gray-500">{{ $swap->requestingShift->date->format('M d') }} {{ $swap->requestingShift->start_time->format('g:i A') }}</p>
                                    @endif
                                </div>
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                    {{ $swap->status->color() === 'gray' ? 'bg-gray-500/10 text-gray-400 ring-gray-500/20' : '' }}
                                    {{ $swap->status->color() === 'yellow' ? 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/20' : '' }}
                                    {{ $swap->status->color() === 'green' ? 'bg-green-500/10 text-green-400 ring-green-500/20' : '' }}
                                    {{ $swap->status->color() === 'red' ? 'bg-red-500/10 text-red-400 ring-red-500/20' : '' }}">
                                    {{ $swap->status->label() }}
                                </span>
                            </div>
                            @if($swap->status === \App\Enums\SwapRequestStatus::Pending && $swap->target_user_id === auth()->id())
                                <div class="mt-3 flex gap-2">
                                    <form action="{{ route('shift-swaps.accept', $swap) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs bg-green-500/10 text-green-400 px-3 py-1.5 rounded-lg hover:bg-green-500/20 transition-colors">Accept</button>
                                    </form>
                                    <form action="{{ route('shift-swaps.reject', $swap) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs bg-red-500/10 text-red-400 px-3 py-1.5 rounded-lg hover:bg-red-500/20 transition-colors">Reject</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center">
                            <p class="text-sm text-gray-500">No active swap requests</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function countdown(targetDatetime) {
            return {
                timeDisplay: 'Loading...',
                targetDate: new Date(targetDatetime),
                interval: null,
                init() {
                    this.update();
                    this.interval = setInterval(() => this.update(), 1000);
                },
                update() {
                    const now = new Date();
                    const diff = this.targetDate - now;

                    if (diff <= 0) {
                        this.timeDisplay = 'Now!';
                        clearInterval(this.interval);
                        return;
                    }

                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

                    if (days > 0) {
                        this.timeDisplay = `${days}d ${hours}h ${minutes}m`;
                    } else if (hours > 0) {
                        this.timeDisplay = `${hours}h ${minutes}m`;
                    } else {
                        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                        this.timeDisplay = `${minutes}m ${seconds}s`;
                    }
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>

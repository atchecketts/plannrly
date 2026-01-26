<x-layouts.mobile title="Time Clock" active="clock" :showHeader="false">
    <!-- Status Bar Spacer -->
    <div class="bg-brand-900 h-6"></div>

    <!-- Header -->
    <header class="bg-brand-900 text-white px-4 pb-6 pt-2">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-brand-200">{{ now()->format('l') }}</p>
                <h1 class="text-xl font-bold">{{ now()->format('F d, Y') }}</h1>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold" id="current-time">{{ now()->format('g:i A') }}</p>
            </div>
        </div>
    </header>

    <div class="px-4 -mt-4 space-y-4">
        @if($todayShift)
            <!-- Today's Shift Card -->
            <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
                <div class="p-4 border-b border-gray-800">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-sm text-gray-500">Today's Shift</p>
                            <p class="font-semibold text-white">
                                {{ $todayShift->start_time->format('g:i A') }} - {{ $todayShift->end_time->format('g:i A') }}
                            </p>
                        </div>
                        @if($activeTimeEntry)
                            <span class="px-3 py-1 bg-green-500/20 text-green-400 text-sm font-medium rounded-full">
                                {{ $activeTimeEntry->status->label() }}
                            </span>
                        @else
                            <span class="px-3 py-1 bg-gray-700 text-gray-300 text-sm font-medium rounded-full">
                                Not Clocked In
                            </span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-500">
                        @if($todayShift->department)
                            {{ $todayShift->department->name }}
                        @endif
                        @if($todayShift->businessRole)
                            @if($todayShift->department) &bull; @endif
                            {{ $todayShift->businessRole->name }}
                        @endif
                        @if($todayShift->location)
                            <br>{{ $todayShift->location->name }}
                        @endif
                    </div>
                </div>

                <!-- Clock Status Section -->
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
                        <!-- Clock In Button -->
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
                <p class="text-sm text-gray-500">You don't have any shifts scheduled for today.</p>
            </div>
        @endif

        <!-- Today's Stats -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-4">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Today's Summary</h3>
            <div class="flex items-center justify-between">
                <span class="text-gray-400">Total Time Worked</span>
                <span class="text-xl font-bold text-white">
                    @php
                        $hours = floor($todayWorkedMinutes / 60);
                        $minutes = $todayWorkedMinutes % 60;
                    @endphp
                    {{ $hours }}h {{ $minutes }}m
                </span>
            </div>
        </div>
    </div>

    <script>
        // Update current time every second
        setInterval(function() {
            const now = new Date();
            let hours = now.getHours();
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            document.getElementById('current-time').textContent = hours + ':' + minutes + ' ' + ampm;
        }, 1000);
    </script>
</x-layouts.mobile>

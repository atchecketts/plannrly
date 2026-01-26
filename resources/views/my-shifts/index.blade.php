<x-layouts.mobile title="My Shifts" active="shifts" :showHeader="false">
    <!-- Status Bar Spacer -->
    <div class="bg-brand-900 h-6"></div>

    <!-- Header -->
    <header class="bg-brand-900 text-white px-4 pb-6 pt-2">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">My Shifts</h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('my-shifts.index', ['start' => $weekStart->copy()->subWeek()->format('Y-m-d')]) }}"
                   class="p-2 bg-white/10 rounded-lg hover:bg-white/20 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <span class="text-sm font-medium">{{ $weekStart->format('M d') }} - {{ $weekEnd->format('M d') }}</span>
                <a href="{{ route('my-shifts.index', ['start' => $weekStart->copy()->addWeek()->format('Y-m-d')]) }}"
                   class="p-2 bg-white/10 rounded-lg hover:bg-white/20 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </header>

    <!-- Week Stats -->
    <div class="px-4 -mt-4">
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-gray-900 rounded-xl p-4 border border-gray-800 text-center">
                <p class="text-2xl font-bold text-brand-400">{{ $totalHours }}h</p>
                <p class="text-xs text-gray-500 mt-1">Total Hours</p>
            </div>
            <div class="bg-gray-900 rounded-xl p-4 border border-gray-800 text-center">
                <p class="text-2xl font-bold text-white">{{ $shiftCount }}</p>
                <p class="text-xs text-gray-500 mt-1">Shifts</p>
            </div>
        </div>
    </div>

    <!-- Shifts by Day -->
    <div class="px-4 mt-6 space-y-4">
        @php
            $currentDate = $weekStart->copy();
        @endphp

        @while($currentDate <= $weekEnd)
            @php
                $dateKey = $currentDate->format('Y-m-d');
                $dayShifts = $shifts[$dateKey] ?? collect();
                $isToday = $currentDate->isToday();
            @endphp

            <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden {{ $isToday ? 'ring-2 ring-brand-500' : '' }}">
                <div class="px-4 py-3 bg-gray-800/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 {{ $isToday ? 'bg-brand-600' : 'bg-gray-800' }} rounded-lg flex flex-col items-center justify-center">
                            <span class="text-[10px] font-medium {{ $isToday ? 'text-brand-200' : 'text-gray-500' }} uppercase">{{ $currentDate->format('D') }}</span>
                            <span class="text-sm font-bold {{ $isToday ? 'text-white' : 'text-gray-300' }}">{{ $currentDate->format('d') }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-white text-sm">{{ $currentDate->format('l') }}</p>
                            <p class="text-xs text-gray-500">{{ $currentDate->format('M d, Y') }}</p>
                        </div>
                    </div>
                    @if($isToday)
                        <span class="px-2 py-1 bg-brand-500/20 text-brand-400 text-xs font-medium rounded-full">Today</span>
                    @endif
                </div>

                @if($dayShifts->isEmpty())
                    <div class="px-4 py-6 text-center">
                        <p class="text-sm text-gray-500">No shifts scheduled</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-800">
                        @foreach($dayShifts as $shift)
                            <div class="px-4 py-3">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="font-medium text-white">
                                            {{ $shift->start_time->format('g:i A') }} - {{ $shift->end_time->format('g:i A') }}
                                        </p>
                                        <p class="text-sm text-gray-500 mt-0.5">
                                            @if($shift->department)
                                                {{ $shift->department->name }}
                                            @endif
                                            @if($shift->businessRole)
                                                @if($shift->department) &bull; @endif
                                                {{ $shift->businessRole->name }}
                                            @endif
                                        </p>
                                        @if($shift->location)
                                            <p class="text-xs text-gray-600 mt-0.5">{{ $shift->location->name }}</p>
                                        @endif
                                        @if($shift->notes)
                                            <p class="text-xs text-gray-500 mt-2 italic">"{{ $shift->notes }}"</p>
                                        @endif
                                    </div>
                                    <div class="text-right flex flex-col items-end gap-2">
                                        <span class="text-sm font-medium text-gray-400">{{ number_format($shift->working_hours, 1) }}h</span>
                                        @if($shift->date >= today())
                                            <a href="{{ route('my-swaps.create', $shift) }}"
                                               class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-brand-400 bg-brand-600/10 rounded-lg hover:bg-brand-600/20 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                </svg>
                                                Swap
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @php
                $currentDate->addDay();
            @endphp
        @endwhile
    </div>

    <div class="h-6"></div>
</x-layouts.mobile>

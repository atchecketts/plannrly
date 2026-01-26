<x-layouts.admin-mobile title="Schedule" active="schedule">
    <!-- Week Navigation -->
    <div class="bg-gray-900 rounded-lg border border-gray-800 p-4 mb-4">
        <div class="flex items-center justify-between">
            <a href="{{ route('schedule.mobile', ['start' => $startDate->copy()->subWeek()->format('Y-m-d')]) }}"
               class="p-2 text-gray-500 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div class="text-center">
                <p class="font-semibold text-white">{{ $startDate->format('M d') }} - {{ $endDate->format('M d') }}</p>
                <p class="text-xs text-gray-500">Week {{ $startDate->weekOfYear }}, {{ $startDate->year }}</p>
            </div>
            <a href="{{ route('schedule.mobile', ['start' => $startDate->copy()->addWeek()->format('Y-m-d')]) }}"
               class="p-2 text-gray-500 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
        @if(!$startDate->isCurrentWeek())
            <div class="mt-3 text-center">
                <a href="{{ route('schedule.mobile') }}" class="text-sm text-brand-400 font-medium">
                    Go to This Week
                </a>
            </div>
        @endif
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="bg-gray-900 rounded-lg p-3 border border-gray-800 text-center">
            <p class="text-xl font-bold text-white">{{ $stats['total_shifts'] }}</p>
            <p class="text-xs text-gray-500">Total Shifts</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-3 border border-gray-800 text-center">
            <p class="text-xl font-bold text-brand-400">{{ $stats['total_hours'] }}h</p>
            <p class="text-xs text-gray-500">Hours</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-3 border {{ $stats['unassigned'] > 0 ? 'border-red-500/30' : 'border-gray-800' }} text-center">
            <p class="text-xl font-bold {{ $stats['unassigned'] > 0 ? 'text-red-400' : 'text-white' }}">{{ $stats['unassigned'] }}</p>
            <p class="text-xs text-gray-500">Unassigned</p>
        </div>
    </div>

    <!-- Day Selector -->
    <div class="flex gap-2 overflow-x-auto pb-2 mb-4 -mx-4 px-4">
        @foreach($weekDates as $date)
            <a href="{{ route('schedule.mobile', ['start' => $startDate->format('Y-m-d'), 'day' => $date->format('Y-m-d')]) }}"
               class="flex-shrink-0 w-14 py-3 rounded-lg text-center transition-colors {{ $selectedDate && $selectedDate->format('Y-m-d') === $date->format('Y-m-d') ? 'bg-brand-900 text-white' : ($date->isToday() ? 'bg-gray-800 text-white' : 'bg-gray-900 text-gray-400 border border-gray-800') }}">
                <p class="text-xs font-medium">{{ $date->format('D') }}</p>
                <p class="text-lg font-bold">{{ $date->format('d') }}</p>
                @if($shiftCounts[$date->format('Y-m-d')] ?? 0 > 0)
                    <div class="w-1.5 h-1.5 bg-brand-400 rounded-full mx-auto mt-1"></div>
                @endif
            </a>
        @endforeach
    </div>

    <!-- Shifts for Selected Day -->
    @if($selectedDate)
        <div class="mb-4">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">
                {{ $selectedDate->format('l, M d') }}
            </h2>

            @if($dayShifts->isEmpty())
                <div class="bg-gray-900 rounded-lg border border-gray-800 p-8 text-center">
                    <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500">No shifts scheduled</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($dayShifts as $shift)
                        <div class="bg-gray-900 rounded-lg border {{ $shift->user_id ? 'border-gray-800' : 'border-red-500/30 border-dashed' }} p-3">
                            <div class="flex items-center gap-3">
                                @if($shift->user)
                                    <div class="w-10 h-10 bg-brand-900/50 rounded-full flex items-center justify-center text-brand-300 font-medium text-sm shrink-0">
                                        {{ substr($shift->user->first_name, 0, 1) }}{{ substr($shift->user->last_name, 0, 1) }}
                                    </div>
                                @else
                                    <div class="w-10 h-10 bg-red-500/20 rounded-full flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    @if($shift->user)
                                        <p class="text-sm font-medium text-white truncate">{{ $shift->user->full_name }}</p>
                                    @else
                                        <p class="text-sm font-medium text-red-400">Unassigned</p>
                                    @endif
                                    <p class="text-xs text-gray-500 truncate">
                                        {{ $shift->department?->name }}
                                        @if($shift->businessRole)
                                            &bull; {{ $shift->businessRole->name }}
                                        @endif
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-medium text-white">{{ $shift->start_time->format('g:ia') }}</p>
                                    <p class="text-xs text-gray-500">{{ $shift->working_hours }}h</p>
                                </div>
                                @if($shift->status->value === 'draft')
                                    <span class="px-2 py-0.5 rounded text-xs font-medium bg-amber-500/20 text-amber-400 shrink-0">
                                        Draft
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        <!-- Show all days summary when no day selected -->
        <div class="space-y-4 mb-4">
            @foreach($weekDates as $date)
                @php
                    $shiftsForDay = $shifts->filter(fn ($s) => $s->date->format('Y-m-d') === $date->format('Y-m-d'));
                @endphp
                @if($shiftsForDay->isNotEmpty())
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-gray-400">{{ $date->format('l, M d') }}</h3>
                            <span class="text-xs text-gray-500">{{ $shiftsForDay->count() }} shifts</span>
                        </div>
                        <div class="space-y-2">
                            @foreach($shiftsForDay->take(3) as $shift)
                                <div class="bg-gray-900 rounded-lg border border-gray-800 p-3 flex items-center gap-3">
                                    @if($shift->user)
                                        <div class="w-8 h-8 bg-brand-900/50 rounded-full flex items-center justify-center text-brand-300 font-medium text-xs shrink-0">
                                            {{ substr($shift->user->first_name, 0, 1) }}{{ substr($shift->user->last_name, 0, 1) }}
                                        </div>
                                    @else
                                        <div class="w-8 h-8 bg-red-500/20 rounded-full flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01" />
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-white truncate">
                                            {{ $shift->user?->full_name ?? 'Unassigned' }}
                                        </p>
                                        <p class="text-xs text-gray-500 truncate">{{ $shift->department?->name }}</p>
                                    </div>
                                    <p class="text-xs text-gray-400 shrink-0">{{ $shift->start_time->format('g:ia') }}</p>
                                </div>
                            @endforeach
                            @if($shiftsForDay->count() > 3)
                                <a href="{{ route('schedule.mobile', ['start' => $startDate->format('Y-m-d'), 'day' => $date->format('Y-m-d')]) }}"
                                   class="block text-center py-2 text-sm text-brand-400 font-medium">
                                    + {{ $shiftsForDay->count() - 3 }} more shifts
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach

            @if($shifts->isEmpty())
                <div class="bg-gray-900 rounded-lg border border-gray-800 p-8 text-center">
                    <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500">No shifts scheduled this week</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 gap-3">
        <a href="{{ route('schedule.index', ['start' => $startDate->format('Y-m-d')]) }}"
           class="flex items-center justify-center gap-2 py-3 bg-gray-900 border border-gray-800 text-white text-sm font-medium rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            Full Grid
        </a>
        <a href="{{ route('schedule.mobile.day', ['date' => ($selectedDate ?? now())->format('Y-m-d')]) }}"
           class="flex items-center justify-center gap-2 py-3 bg-brand-900 text-white text-sm font-medium rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Day View
        </a>
    </div>
</x-layouts.admin-mobile>

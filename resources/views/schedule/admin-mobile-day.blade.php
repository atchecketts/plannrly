<x-layouts.admin-mobile title="Day Schedule" active="schedule">
    <!-- Day Navigation -->
    <div class="bg-gray-900 rounded-lg border border-gray-800 p-4 mb-4">
        <div class="flex items-center justify-between">
            <a href="{{ route('schedule.mobile.day', ['date' => $selectedDate->copy()->subDay()->format('Y-m-d')]) }}"
               class="p-2 text-gray-500 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div class="text-center">
                <p class="font-semibold text-white">{{ $selectedDate->format('l, M d') }}</p>
                <p class="text-xs text-gray-500">{{ $selectedDate->isToday() ? 'Today' : $selectedDate->format('Y') }}</p>
            </div>
            <a href="{{ route('schedule.mobile.day', ['date' => $selectedDate->copy()->addDay()->format('Y-m-d')]) }}"
               class="p-2 text-gray-500 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
        @if(!$selectedDate->isToday())
            <div class="mt-3 text-center">
                <a href="{{ route('schedule.mobile.day') }}" class="text-sm text-brand-400 font-medium">
                    Go to Today
                </a>
            </div>
        @endif
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="bg-gray-900 rounded-lg p-3 border border-gray-800 text-center">
            <p class="text-xl font-bold text-white">{{ $totalShifts }}</p>
            <p class="text-xs text-gray-500">Shifts</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-3 border border-gray-800 text-center">
            <p class="text-xl font-bold text-brand-400">{{ round($totalHours) }}h</p>
            <p class="text-xs text-gray-500">Hours</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-3 border {{ $unassignedShiftsCount > 0 ? 'border-red-500/30' : 'border-gray-800' }} text-center">
            <p class="text-xl font-bold {{ $unassignedShiftsCount > 0 ? 'text-red-400' : 'text-white' }}">{{ $unassignedShiftsCount }}</p>
            <p class="text-xs text-gray-500">Unassigned</p>
        </div>
    </div>

    <!-- Shifts List -->
    <div class="mb-4">
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Shifts</h2>

        @if($shifts->isEmpty())
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
                @foreach($shifts->sortBy('start_time') as $shift)
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
                                <p class="text-sm font-medium text-white">{{ $shift->start_time->format('g:ia') }} - {{ $shift->end_time->format('g:ia') }}</p>
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

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 gap-3">
        <a href="{{ route('schedule.mobile') }}"
           class="flex items-center justify-center gap-2 py-3 bg-gray-900 border border-gray-800 text-white text-sm font-medium rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Week View
        </a>
        <a href="{{ route('schedule.day', ['date' => $selectedDate->format('Y-m-d')]) }}"
           class="flex items-center justify-center gap-2 py-3 bg-brand-900 text-white text-sm font-medium rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            Full Grid
        </a>
    </div>
</x-layouts.admin-mobile>

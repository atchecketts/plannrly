<x-layouts.app title="My Timesheet">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-white">My Timesheet</h2>
                    <p class="mt-1 text-sm text-gray-400">
                        Week of {{ $weekStart->format('M d') }} - {{ $weekEnd->format('M d, Y') }}
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('timesheets.employee', ['week_start' => $prevWeek->format('Y-m-d')]) }}"
                           class="border border-gray-700 text-gray-300 py-2 px-3 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                        <a href="{{ route('timesheets.employee') }}"
                           class="border border-gray-700 text-gray-300 py-2 px-4 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
                            This Week
                        </a>
                        <a href="{{ route('timesheets.employee', ['week_start' => $nextWeek->format('Y-m-d')]) }}"
                           class="border border-gray-700 text-gray-300 py-2 px-3 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>

                    <!-- Export Button -->
                    <a href="{{ route('timesheets.export', ['week_start' => $weekStart->format('Y-m-d')]) }}"
                       class="flex items-center gap-2 border border-gray-700 text-gray-300 py-2 px-4 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
            <p class="text-sm text-gray-500">Scheduled</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $weeklyTotals['scheduled_hours'] }}h</p>
        </div>
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
            <p class="text-sm text-gray-500">Worked</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $weeklyTotals['actual_hours'] }}h</p>
        </div>
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
            <p class="text-sm text-gray-500">Variance</p>
            @php $variance = $weeklyTotals['variance_minutes']; @endphp
            <p class="text-2xl font-bold mt-1 {{ $variance > 0 ? 'text-orange-400' : ($variance < 0 ? 'text-red-400' : 'text-green-400') }}">
                {{ $variance >= 0 ? '+' : '' }}{{ round($variance / 60, 1) }}h
            </p>
        </div>
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
            <p class="text-sm text-gray-500">Breaks</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $weeklyTotals['break_hours'] }}h</p>
        </div>
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
            <p class="text-sm text-gray-500">Entries</p>
            <div class="flex items-center gap-2 mt-1">
                <p class="text-2xl font-bold text-white">{{ $weeklyTotals['entry_count'] }}</p>
                @if($weeklyTotals['pending_approval'] > 0)
                    <span class="inline-flex items-center rounded-md bg-amber-500/10 px-2 py-1 text-xs font-medium text-amber-400 ring-1 ring-inset ring-amber-500/20">
                        {{ $weeklyTotals['pending_approval'] }} pending
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Week Grid View -->
    <div class="bg-gray-900 rounded-lg border border-gray-800 overflow-hidden mb-6">
        <div class="grid grid-cols-7 divide-x divide-gray-800">
            @foreach($weekDays as $day)
                @php
                    $dayKey = $day->format('Y-m-d');
                    $dayEntries = $entriesByDate->get($dayKey, collect());
                    $dayTotal = $dayEntries->sum('total_worked_minutes');
                @endphp
                <div class="min-h-[120px] {{ $day->isToday() ? 'bg-brand-900/10' : '' }}">
                    <div class="p-3 border-b border-gray-800 {{ $day->isToday() ? 'bg-brand-900/20' : 'bg-gray-800/50' }}">
                        <p class="text-xs text-gray-500 uppercase">{{ $day->format('D') }}</p>
                        <div class="flex items-center justify-between">
                            <p class="text-lg font-semibold {{ $day->isToday() ? 'text-brand-400' : 'text-white' }}">{{ $day->format('d') }}</p>
                            @if($dayTotal > 0)
                                <span class="text-xs text-gray-400">{{ round($dayTotal / 60, 1) }}h</span>
                            @endif
                        </div>
                    </div>
                    <div class="p-2 space-y-1">
                        @foreach($dayEntries as $entry)
                            <a href="{{ route('time-entries.show', $entry) }}"
                               class="block p-2 rounded-lg bg-gray-800/50 hover:bg-gray-800 transition-colors">
                                <div class="flex items-center gap-1.5">
                                    @if($entry->shift?->businessRole)
                                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $entry->shift->businessRole->color }}"></span>
                                    @endif
                                    <span class="text-xs text-white truncate">
                                        {{ $entry->clock_in_at->format('g:i A') }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $entry->total_worked_hours ?? '-' }}h
                                    @if($entry->isApproved())
                                        <span class="text-green-400">&#10003;</span>
                                    @elseif($entry->requiresApproval())
                                        <span class="text-amber-400">&#8987;</span>
                                    @endif
                                </p>
                            </a>
                        @endforeach
                        @if($dayEntries->isEmpty() && $day->isPast())
                            <p class="text-xs text-gray-600 text-center py-2">No entry</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Detailed List View -->
    <div class="bg-gray-900 rounded-lg border border-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800">
            <h3 class="text-base font-semibold text-white">Detailed View</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-800">
            <thead>
                <tr class="bg-gray-800/50">
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-300 uppercase">Date</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 uppercase">Scheduled</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 uppercase">Actual</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 uppercase">Hours</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 uppercase">Variance</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 uppercase">Status</th>
                    <th class="relative py-3 pl-3 pr-6"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($timeEntries as $entry)
                    <tr class="hover:bg-gray-800/50 transition-colors">
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            <div class="text-white font-medium">{{ $entry->clock_in_at->format('l, M d') }}</div>
                            @if($entry->shift?->businessRole)
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $entry->shift->businessRole->color }}"></span>
                                    <span class="text-xs text-gray-500">{{ $entry->shift->businessRole->name }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">
                            @if($entry->shift)
                                <div>{{ $entry->shift->start_time->format('g:i A') }}</div>
                                <div>{{ $entry->shift->end_time->format('g:i A') }}</div>
                            @else
                                <span class="text-gray-600">-</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            <div class="text-white">{{ $entry->clock_in_at->format('g:i A') }}</div>
                            <div class="text-gray-500">{{ $entry->clock_out_at?->format('g:i A') ?? 'In progress' }}</div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            <div class="text-white font-medium">{{ $entry->total_worked_hours ?? '-' }}h</div>
                            @if($entry->actual_break_minutes)
                                <div class="text-xs text-gray-500">{{ $entry->actual_break_minutes }}m break</div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            @if($entry->shift && $entry->clock_out_at)
                                @php $varianceStatus = $entry->variance_status; @endphp
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                    {{ $varianceStatus['color'] === 'green' ? 'bg-green-500/10 text-green-400 ring-green-500/20' : '' }}
                                    {{ $varianceStatus['color'] === 'yellow' ? 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/20' : '' }}
                                    {{ $varianceStatus['color'] === 'red' ? 'bg-red-500/10 text-red-400 ring-red-500/20' : '' }}
                                    {{ $varianceStatus['color'] === 'orange' ? 'bg-orange-500/10 text-orange-400 ring-orange-500/20' : '' }}
                                    {{ $varianceStatus['color'] === 'gray' ? 'bg-gray-500/10 text-gray-400 ring-gray-500/20' : '' }}">
                                    {{ $varianceStatus['label'] }}
                                </span>
                            @else
                                <span class="text-gray-600">-</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            @if($entry->isApproved())
                                <span class="inline-flex items-center rounded-md bg-green-500/10 px-2 py-1 text-xs font-medium text-green-400 ring-1 ring-inset ring-green-500/20">
                                    Approved
                                </span>
                            @elseif($entry->isClockedOut() && $entry->requiresApproval())
                                <span class="inline-flex items-center rounded-md bg-amber-500/10 px-2 py-1 text-xs font-medium text-amber-400 ring-1 ring-inset ring-amber-500/20">
                                    Pending Approval
                                </span>
                            @elseif($entry->isClockedOut())
                                <span class="inline-flex items-center rounded-md bg-gray-500/10 px-2 py-1 text-xs font-medium text-gray-400 ring-1 ring-inset ring-gray-500/20">
                                    Complete
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-blue-500/10 px-2 py-1 text-xs font-medium text-blue-400 ring-1 ring-inset ring-blue-500/20">
                                    In Progress
                                </span>
                            @endif
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                            <a href="{{ route('time-entries.show', $entry) }}" class="text-brand-400 hover:text-brand-300">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                            No time entries found for this week.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>

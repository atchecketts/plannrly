<x-layouts.app title="Timesheets">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-white">Timesheets</h2>
                    <p class="mt-1 text-sm text-gray-400">
                        Week of {{ $weekStart->format('M d') }} - {{ $weekEnd->format('M d, Y') }}
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('timesheets.index', ['week_start' => $prevWeek->format('Y-m-d')]) }}"
                           class="border border-gray-700 text-gray-300 py-2 px-3 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                        <a href="{{ route('timesheets.index') }}"
                           class="border border-gray-700 text-gray-300 py-2 px-4 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
                            Today
                        </a>
                        <a href="{{ route('timesheets.index', ['week_start' => $nextWeek->format('Y-m-d')]) }}"
                           class="border border-gray-700 text-gray-300 py-2 px-3 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>

                    <!-- Export Buttons -->
                    <div class="flex items-center gap-2" x-data="{ open: false }">
                        <div class="relative">
                            <button @click="open = !open" type="button" class="flex items-center gap-2 border border-gray-700 text-gray-300 py-2 px-4 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Export
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition
                                 class="absolute right-0 mt-2 w-48 bg-gray-800 border border-gray-700 rounded-lg shadow-lg z-10">
                                <a href="{{ route('timesheets.export', array_merge(request()->only(['week_start', 'user_id', 'department_id']), [])) }}"
                                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 rounded-t-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Detailed CSV
                                </a>
                                @if(!auth()->user()->isEmployee())
                                    <a href="{{ route('timesheets.export.payroll', array_merge(request()->only(['week_start', 'user_id', 'department_id']), [])) }}"
                                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 rounded-b-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Payroll CSV
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    @if(!auth()->user()->isEmployee())
        <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
            <div class="px-6 py-4">
                <form method="GET" action="{{ route('timesheets.index') }}" class="flex flex-wrap items-center gap-4">
                    <input type="hidden" name="week_start" value="{{ $weekStart->format('Y-m-d') }}">

                    <div class="flex items-center gap-2">
                        <label for="user_id" class="text-sm text-gray-400">Employee:</label>
                        <select name="user_id" id="user_id" class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2">
                            <option value="">All Employees</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ request('user_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <label for="department_id" class="text-sm text-gray-400">Department:</label>
                        <select name="department_id" id="department_id" class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="bg-brand-900 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-brand-800 transition-colors">
                        Filter
                    </button>

                    @if(request()->hasAny(['user_id', 'department_id']))
                        <a href="{{ route('timesheets.index', ['week_start' => $weekStart->format('Y-m-d')]) }}" class="text-sm text-gray-400 hover:text-white">Clear</a>
                    @endif
                </form>
            </div>
        </div>
    @endif

    <!-- Week Overview Header -->
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6 overflow-hidden">
        <div class="grid grid-cols-7 divide-x divide-gray-800">
            @foreach($weekDays as $day)
                <div class="p-3 text-center {{ $day->isToday() ? 'bg-brand-900/20' : '' }}">
                    <p class="text-xs text-gray-500 uppercase">{{ $day->format('D') }}</p>
                    <p class="text-lg font-semibold {{ $day->isToday() ? 'text-brand-400' : 'text-white' }}">{{ $day->format('d') }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Timesheets by Employee -->
    @if($settings?->require_manager_approval && !auth()->user()->isEmployee())
        <form method="POST" action="{{ route('timesheets.approve-multiple') }}" id="batch-approve-form">
            @csrf
    @endif

    @forelse($groupedByUser as $userId => $entries)
        @php
            $employee = $entries->first()->user;
            $totals = $userTotals[$userId] ?? [];
        @endphp
        <div class="bg-gray-900 rounded-lg border border-gray-800 mb-4 overflow-hidden">
            <!-- Employee Header -->
            <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-brand-500/20 rounded-full flex items-center justify-center text-brand-400 font-medium">
                        {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-white">{{ $employee->full_name }}</h3>
                        <p class="text-xs text-gray-500">{{ $entries->count() }} {{ Str::plural('entry', $entries->count()) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <div class="text-center">
                        <p class="text-gray-500">Scheduled</p>
                        <p class="text-white font-medium">{{ $totals['scheduled_hours'] ?? 0 }}h</p>
                    </div>
                    <div class="text-center">
                        <p class="text-gray-500">Actual</p>
                        <p class="text-white font-medium">{{ $totals['actual_hours'] ?? 0 }}h</p>
                    </div>
                    <div class="text-center">
                        <p class="text-gray-500">Variance</p>
                        @php $variance = $totals['variance_minutes'] ?? 0; @endphp
                        <p class="font-medium {{ $variance > 0 ? 'text-orange-400' : ($variance < 0 ? 'text-red-400' : 'text-green-400') }}">
                            {{ $variance >= 0 ? '+' : '' }}{{ round($variance / 60, 1) }}h
                        </p>
                    </div>
                    @if($totals['pending_approval'] ?? 0 > 0)
                        <div class="text-center">
                            <span class="inline-flex items-center rounded-md bg-amber-500/10 px-2 py-1 text-xs font-medium text-amber-400 ring-1 ring-inset ring-amber-500/20">
                                {{ $totals['pending_approval'] }} pending
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Entries Table -->
            <table class="min-w-full divide-y divide-gray-800">
                <thead>
                    <tr class="bg-gray-800/50">
                        @if($settings?->require_manager_approval && !auth()->user()->isEmployee())
                            <th class="w-12 py-3 pl-6 pr-3">
                                <input type="checkbox" class="rounded bg-gray-800 border-gray-700 select-all-user" data-user="{{ $userId }}">
                            </th>
                        @endif
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 uppercase">Date</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 uppercase">Scheduled</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 uppercase">Actual</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 uppercase">Hours</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 uppercase">Variance</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-300 uppercase">Status</th>
                        <th class="relative py-3 pl-3 pr-6"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($entries as $entry)
                        <tr class="hover:bg-gray-800/50 transition-colors">
                            @if($settings?->require_manager_approval && !auth()->user()->isEmployee())
                                <td class="py-4 pl-6 pr-3">
                                    @if(!$entry->isApproved() && $entry->isClockedOut())
                                        <input type="checkbox" name="entry_ids[]" value="{{ $entry->id }}" class="rounded bg-gray-800 border-gray-700 entry-checkbox" data-user="{{ $userId }}">
                                    @endif
                                </td>
                            @endif
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                <div class="text-white font-medium">{{ $entry->clock_in_at->format('D, M d') }}</div>
                                @if($entry->shift?->businessRole)
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $entry->shift->businessRole->color }}"></span>
                                        <span class="text-xs text-gray-500">{{ $entry->shift->businessRole->name }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">
                                @if($entry->shift)
                                    {{ $entry->shift->start_time->format('g:i A') }} - {{ $entry->shift->end_time->format('g:i A') }}
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
                                @if($entry->shift && $entry->clock_in_at)
                                    @php $clockInStatus = $entry->clock_in_status; @endphp
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                        {{ $clockInStatus['color'] === 'green' ? 'bg-green-500/10 text-green-400 ring-green-500/20' : '' }}
                                        {{ $clockInStatus['color'] === 'yellow' ? 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/20' : '' }}
                                        {{ $clockInStatus['color'] === 'red' ? 'bg-red-500/10 text-red-400 ring-red-500/20' : '' }}
                                        {{ $clockInStatus['color'] === 'blue' ? 'bg-blue-500/10 text-blue-400 ring-blue-500/20' : '' }}
                                        {{ $clockInStatus['color'] === 'gray' ? 'bg-gray-500/10 text-gray-400 ring-gray-500/20' : '' }}">
                                        {{ $clockInStatus['label'] }}
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
                                        Pending
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
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-12 text-center">
            <svg class="w-12 h-12 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-gray-500">No time entries found for this week.</p>
        </div>
    @endforelse

    @if($settings?->require_manager_approval && !auth()->user()->isEmployee() && $timeEntries->isNotEmpty())
        <div class="mt-4 flex justify-end">
            <button type="submit" class="bg-green-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-green-500 transition-colors">
                Approve Selected
            </button>
        </div>
        </form>

        @push('scripts')
        <script>
            document.querySelectorAll('.select-all-user').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const userId = this.dataset.user;
                    document.querySelectorAll(`.entry-checkbox[data-user="${userId}"]`).forEach(cb => {
                        cb.checked = this.checked;
                    });
                });
            });
        </script>
        @endpush
    @endif
</x-layouts.app>

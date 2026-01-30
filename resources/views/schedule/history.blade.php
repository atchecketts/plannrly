<x-layouts.app title="Schedule History">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">Schedule History</h2>
                    <p class="mt-1 text-sm text-gray-400">A timeline of all schedule changes.</p>
                </div>
                <a href="{{ route('schedule.index') }}" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <form method="GET" action="{{ route('schedule.history') }}" class="px-6 py-4">
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-400 mb-1">From</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate->format('Y-m-d') }}"
                           class="bg-gray-800 border-gray-700 text-white rounded-lg focus:ring-brand-500 focus:border-brand-500 text-sm px-3 py-2">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-400 mb-1">To</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate->format('Y-m-d') }}"
                           class="bg-gray-800 border-gray-700 text-white rounded-lg focus:ring-brand-500 focus:border-brand-500 text-sm px-3 py-2">
                </div>
                <div>
                    <label for="action" class="block text-sm font-medium text-gray-400 mb-1">Action</label>
                    <select name="action" id="action"
                            class="bg-gray-800 border-gray-700 text-white rounded-lg focus:ring-brand-500 focus:border-brand-500 text-sm px-3 py-2">
                        <option value="">All Actions</option>
                        <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>Created</option>
                        <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>Updated</option>
                        <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                    </select>
                </div>
                <div>
                    <label for="changed_by" class="block text-sm font-medium text-gray-400 mb-1">Changed By</label>
                    <select name="changed_by" id="changed_by"
                            class="bg-gray-800 border-gray-700 text-white rounded-lg focus:ring-brand-500 focus:border-brand-500 text-sm px-3 py-2">
                        <option value="">All Users</option>
                        @foreach($changers as $changer)
                            <option value="{{ $changer->id }}" {{ request('changed_by') == $changer->id ? 'selected' : '' }}>
                                {{ $changer->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-brand-900 text-white py-2 px-4 rounded-lg font-medium hover:bg-brand-800 transition-colors text-sm">
                    Filter
                </button>
                <a href="{{ route('schedule.history') }}" class="text-gray-400 hover:text-white py-2 px-4 text-sm transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Timeline -->
    <div class="bg-gray-900 rounded-lg border border-gray-800 overflow-hidden">
        @if($history->isEmpty())
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-4 text-gray-400">No schedule history found for this period.</p>
            </div>
        @else
            <div class="divide-y divide-gray-800">
                @foreach($history as $entry)
                    <div class="px-6 py-4 hover:bg-gray-800/50 transition-colors">
                        <div class="flex items-start gap-4">
                            <!-- Action Icon -->
                            <div class="flex-shrink-0 mt-0.5">
                                @if($entry->action->value === 'created')
                                    <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </div>
                                @elseif($entry->action->value === 'updated')
                                    <div class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-medium text-white">{{ $entry->user->full_name }}</span>
                                    <span class="text-gray-500">{{ $entry->action->label() }}</span>
                                    @if($entry->shift)
                                        <span class="text-gray-400">shift for</span>
                                        <span class="text-white">
                                            {{ $entry->shift->user?->full_name ?? 'Unassigned' }}
                                        </span>
                                        <span class="text-gray-500">on</span>
                                        <span class="text-gray-300">{{ $entry->shift->date->format('M j, Y') }}</span>
                                    @else
                                        <span class="text-gray-500">a shift (now deleted)</span>
                                    @endif
                                </div>

                                <!-- Change Details -->
                                @if($entry->action->value === 'updated' && $entry->old_values && $entry->new_values)
                                    <div class="mt-2 text-sm">
                                        <ul class="space-y-1">
                                            @foreach($entry->new_values as $field => $newValue)
                                                @php
                                                    $oldValue = $entry->old_values[$field] ?? null;
                                                    $fieldLabel = match($field) {
                                                        'user_id' => 'Assigned to',
                                                        'date' => 'Date',
                                                        'start_time' => 'Start time',
                                                        'end_time' => 'End time',
                                                        'break_duration_minutes' => 'Break',
                                                        'notes' => 'Notes',
                                                        'status' => 'Status',
                                                        'location_id' => 'Location',
                                                        'department_id' => 'Department',
                                                        'business_role_id' => 'Role',
                                                        default => ucfirst(str_replace('_', ' ', $field)),
                                                    };
                                                @endphp
                                                <li class="flex items-center gap-2 text-gray-400">
                                                    <span class="text-gray-500">{{ $fieldLabel }}:</span>
                                                    <span class="line-through text-gray-600">{{ $oldValue ?? 'empty' }}</span>
                                                    <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                    </svg>
                                                    <span class="text-gray-300">{{ $newValue ?? 'empty' }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $entry->created_at->format('M j, Y \a\t g:i A') }}
                                    <span class="text-gray-600">&bull;</span>
                                    {{ $entry->created_at->diffForHumans() }}
                                </div>
                            </div>

                            <!-- View Shift Link -->
                            @if($entry->shift && !$entry->shift->trashed())
                                <a href="{{ route('schedule.history.shift', $entry->shift) }}"
                                   class="text-gray-500 hover:text-white transition-colors text-sm">
                                    View shift history
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if($history->hasPages())
        <div class="mt-4">
            {{ $history->links() }}
        </div>
    @endif
</x-layouts.app>

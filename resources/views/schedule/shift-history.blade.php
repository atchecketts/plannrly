<x-layouts.app title="Shift History">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                        <a href="{{ route('schedule.history') }}" class="hover:text-white transition-colors">Schedule History</a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="text-gray-400">Shift History</span>
                    </div>
                    <h2 class="text-lg font-semibold text-white">
                        History for {{ $shift->date->format('l, M j, Y') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-400">
                        {{ $shift->start_time->format('g:i A') }} - {{ $shift->end_time->format('g:i A') }}
                        @if($shift->user)
                            &bull; {{ $shift->user->full_name }}
                        @else
                            &bull; Unassigned
                        @endif
                        @if($shift->department)
                            &bull; {{ $shift->department->name }}
                        @endif
                    </p>
                </div>
                <a href="{{ route('schedule.history') }}" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <div class="bg-gray-900 rounded-lg border border-gray-800 overflow-hidden">
        @if($history->isEmpty())
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-4 text-gray-400">No history recorded for this shift.</p>
            </div>
        @else
            <div class="relative">
                <!-- Timeline Line -->
                <div class="absolute left-10 top-0 bottom-0 w-px bg-gray-800"></div>

                <div class="divide-y divide-gray-800">
                    @foreach($history as $entry)
                        <div class="px-6 py-4 relative">
                            <div class="flex items-start gap-4">
                                <!-- Action Icon (overlays the timeline line) -->
                                <div class="flex-shrink-0 relative z-10">
                                    @if($entry->action->value === 'created')
                                        <div class="w-8 h-8 rounded-full bg-gray-900 border-2 border-green-500 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </div>
                                    @elseif($entry->action->value === 'updated')
                                        <div class="w-8 h-8 rounded-full bg-gray-900 border-2 border-blue-500 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-gray-900 border-2 border-red-500 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0 pt-0.5">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-white">{{ $entry->user->full_name }}</span>
                                            <span class="text-gray-500">{{ $entry->action->label() }} this shift</span>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $entry->created_at->diffForHumans() }}</span>
                                    </div>

                                    <!-- Change Details -->
                                    @if($entry->action->value === 'updated' && $entry->old_values && $entry->new_values)
                                        <div class="mt-3 bg-gray-800/50 rounded-lg p-3">
                                            <div class="text-xs font-medium text-gray-400 mb-2">Changes:</div>
                                            <div class="space-y-2">
                                                @foreach($entry->new_values as $field => $newValue)
                                                    @php
                                                        $oldValue = $entry->old_values[$field] ?? null;
                                                        $fieldLabel = match($field) {
                                                            'user_id' => 'Assigned to',
                                                            'date' => 'Date',
                                                            'start_time' => 'Start time',
                                                            'end_time' => 'End time',
                                                            'break_duration_minutes' => 'Break duration',
                                                            'notes' => 'Notes',
                                                            'status' => 'Status',
                                                            'location_id' => 'Location',
                                                            'department_id' => 'Department',
                                                            'business_role_id' => 'Role',
                                                            default => ucfirst(str_replace('_', ' ', $field)),
                                                        };
                                                    @endphp
                                                    <div class="flex items-start gap-3 text-sm">
                                                        <span class="text-gray-500 w-28 flex-shrink-0">{{ $fieldLabel }}</span>
                                                        <div class="flex items-center gap-2 flex-1 min-w-0">
                                                            <span class="px-2 py-0.5 bg-red-500/20 text-red-400 rounded text-xs truncate">{{ $oldValue ?? 'empty' }}</span>
                                                            <svg class="w-4 h-4 text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                            </svg>
                                                            <span class="px-2 py-0.5 bg-green-500/20 text-green-400 rounded text-xs truncate">{{ $newValue ?? 'empty' }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @elseif($entry->action->value === 'created' && $entry->new_values)
                                        <div class="mt-3 bg-gray-800/50 rounded-lg p-3">
                                            <div class="text-xs font-medium text-gray-400 mb-2">Initial values:</div>
                                            <div class="grid grid-cols-2 gap-2 text-sm">
                                                @foreach(['date', 'start_time', 'end_time', 'status'] as $field)
                                                    @if(isset($entry->new_values[$field]))
                                                        @php
                                                            $fieldLabel = match($field) {
                                                                'date' => 'Date',
                                                                'start_time' => 'Start',
                                                                'end_time' => 'End',
                                                                'status' => 'Status',
                                                                default => ucfirst($field),
                                                            };
                                                        @endphp
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-gray-500">{{ $fieldLabel }}:</span>
                                                            <span class="text-gray-300">{{ $entry->new_values[$field] }}</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-2 text-xs text-gray-500">
                                        {{ $entry->created_at->format('l, M j, Y \a\t g:i A') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>

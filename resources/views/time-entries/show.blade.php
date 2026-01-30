<x-layouts.app title="Time Entry Details">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4 flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="text-lg font-semibold text-white">Time Entry Details</h2>
                <p class="mt-1 text-sm text-gray-400">
                    {{ $timeEntry->user->full_name }} - {{ $timeEntry->clock_in_at?->format('M d, Y') ?? 'Not clocked in' }}
                </p>
            </div>
            <div class="mt-4 flex gap-3 lg:ml-4 lg:mt-0">
                <a href="{{ route('time-entries.index') }}" class="border border-gray-700 text-gray-300 py-2.5 px-4 rounded-lg font-medium hover:bg-gray-800 transition-colors">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="bg-gray-900 rounded-lg border border-gray-800">
            <div class="px-6 py-4 border-b border-gray-800">
                <h3 class="text-base font-semibold text-white">Clock Times</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-400">Status</dt>
                    <dd>
                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                            {{ $timeEntry->status->color() === 'green' ? 'bg-green-500/10 text-green-400 ring-green-500/20' : '' }}
                            {{ $timeEntry->status->color() === 'yellow' ? 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/20' : '' }}
                            {{ $timeEntry->status->color() === 'gray' ? 'bg-gray-500/10 text-gray-400 ring-gray-500/20' : '' }}">
                            {{ $timeEntry->status->label() }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-400">Clock In</dt>
                    <dd class="text-sm text-white">{{ $timeEntry->clock_in_at?->format('M d, Y g:i A') ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-400">Clock Out</dt>
                    <dd class="text-sm text-white">{{ $timeEntry->clock_out_at?->format('M d, Y g:i A') ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-400">Break Duration</dt>
                    <dd class="text-sm text-white">{{ $timeEntry->actual_break_minutes ?? 0 }} minutes</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-400">Total Worked</dt>
                    <dd class="text-sm text-white font-medium">{{ $timeEntry->total_worked_hours ?? '-' }} hours</dd>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 rounded-lg border border-gray-800">
            <div class="px-6 py-4 border-b border-gray-800">
                <h3 class="text-base font-semibold text-white">Shift Information</h3>
            </div>
            <div class="p-6 space-y-4">
                @if($timeEntry->shift)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-400">Date</dt>
                        <dd class="text-sm text-white">{{ $timeEntry->shift->date->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-400">Scheduled Time</dt>
                        <dd class="text-sm text-white">{{ $timeEntry->shift->start_time->format('g:i A') }} - {{ $timeEntry->shift->end_time->format('g:i A') }}</dd>
                    </div>
                    @if($timeEntry->shift->businessRole)
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-400">Role</dt>
                            <dd class="flex items-center gap-2 text-sm text-white">
                                <span class="w-2 h-2 rounded-full" style="background-color: {{ $timeEntry->shift->businessRole->color }}"></span>
                                {{ $timeEntry->shift->businessRole->name }}
                            </dd>
                        </div>
                    @endif
                    @if($timeEntry->shift->department)
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-400">Department</dt>
                            <dd class="text-sm text-white">{{ $timeEntry->shift->department->name }}</dd>
                        </div>
                    @endif
                    @if($timeEntry->shift->location)
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-400">Location</dt>
                            <dd class="text-sm text-white">{{ $timeEntry->shift->location->name }}</dd>
                        </div>
                    @endif
                @else
                    <p class="text-sm text-gray-500">No shift associated.</p>
                @endif
            </div>
        </div>

        @if($timeEntry->shift && $timeEntry->clock_in_at)
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="text-base font-semibold text-white">Time Variance</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-400">Clock In</dt>
                        <dd>
                            @php $clockInStatus = $timeEntry->clock_in_status; @endphp
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                {{ $clockInStatus['color'] === 'green' ? 'bg-green-500/10 text-green-400 ring-green-500/20' : '' }}
                                {{ $clockInStatus['color'] === 'yellow' ? 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/20' : '' }}
                                {{ $clockInStatus['color'] === 'red' ? 'bg-red-500/10 text-red-400 ring-red-500/20' : '' }}
                                {{ $clockInStatus['color'] === 'blue' ? 'bg-blue-500/10 text-blue-400 ring-blue-500/20' : '' }}
                                {{ $clockInStatus['color'] === 'gray' ? 'bg-gray-500/10 text-gray-400 ring-gray-500/20' : '' }}">
                                {{ $clockInStatus['label'] }}
                            </span>
                        </dd>
                    </div>
                    @if($timeEntry->clock_out_at)
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-400">Clock Out</dt>
                            <dd>
                                @php $clockOutStatus = $timeEntry->clock_out_status; @endphp
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                    {{ $clockOutStatus['color'] === 'green' ? 'bg-green-500/10 text-green-400 ring-green-500/20' : '' }}
                                    {{ $clockOutStatus['color'] === 'yellow' ? 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/20' : '' }}
                                    {{ $clockOutStatus['color'] === 'red' ? 'bg-red-500/10 text-red-400 ring-red-500/20' : '' }}
                                    {{ $clockOutStatus['color'] === 'orange' ? 'bg-orange-500/10 text-orange-400 ring-orange-500/20' : '' }}
                                    {{ $clockOutStatus['color'] === 'gray' ? 'bg-gray-500/10 text-gray-400 ring-gray-500/20' : '' }}">
                                    {{ $clockOutStatus['label'] }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-400">Total Variance</dt>
                            <dd>
                                @php $varianceStatus = $timeEntry->variance_status; @endphp
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                    {{ $varianceStatus['color'] === 'green' ? 'bg-green-500/10 text-green-400 ring-green-500/20' : '' }}
                                    {{ $varianceStatus['color'] === 'yellow' ? 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/20' : '' }}
                                    {{ $varianceStatus['color'] === 'red' ? 'bg-red-500/10 text-red-400 ring-red-500/20' : '' }}
                                    {{ $varianceStatus['color'] === 'orange' ? 'bg-orange-500/10 text-orange-400 ring-orange-500/20' : '' }}
                                    {{ $varianceStatus['color'] === 'gray' ? 'bg-gray-500/10 text-gray-400 ring-gray-500/20' : '' }}">
                                    {{ $varianceStatus['label'] }}
                                </span>
                            </dd>
                        </div>
                    @endif
                    <div class="pt-3 border-t border-gray-800">
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>Scheduled: {{ $timeEntry->scheduled_duration_minutes ?? '-' }}m</span>
                            <span>Actual: {{ $timeEntry->total_worked_minutes ?? '-' }}m</span>
                            <span>Diff: {{ $timeEntry->variance_minutes !== null ? $timeEntry->formatVariance($timeEntry->variance_minutes) : '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($timeEntry->clock_in_location || $timeEntry->clock_out_location)
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="text-base font-semibold text-white">Location Data</h3>
                </div>
                <div class="p-6 space-y-4">
                    @if($timeEntry->clock_in_location)
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-400">Clock In Location</dt>
                            <dd class="text-sm text-white">
                                {{ $timeEntry->clock_in_location['lat'] ?? '-' }}, {{ $timeEntry->clock_in_location['lng'] ?? '-' }}
                            </dd>
                        </div>
                    @endif
                    @if($timeEntry->clock_out_location)
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-400">Clock Out Location</dt>
                            <dd class="text-sm text-white">
                                {{ $timeEntry->clock_out_location['lat'] ?? '-' }}, {{ $timeEntry->clock_out_location['lng'] ?? '-' }}
                            </dd>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="bg-gray-900 rounded-lg border border-gray-800">
            <div class="px-6 py-4 border-b border-gray-800">
                <h3 class="text-base font-semibold text-white">Approval Status</h3>
            </div>
            <div class="p-6 space-y-4">
                @if($timeEntry->isApproved())
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-400">Approved By</dt>
                        <dd class="text-sm text-white">{{ $timeEntry->approvedBy->full_name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-400">Approved At</dt>
                        <dd class="text-sm text-white">{{ $timeEntry->approved_at->format('M d, Y g:i A') }}</dd>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Not yet approved.</p>
                    @can('approve', $timeEntry)
                        <form action="{{ route('time-entries.approve', $timeEntry) }}" method="POST" class="mt-4">
                            @csrf
                            <button type="submit" class="bg-green-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-green-500 transition-colors">
                                Approve Time Entry
                            </button>
                        </form>
                    @endcan
                @endif
            </div>
        </div>

        @if($timeEntry->adjustment_reason)
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="text-base font-semibold text-white">Adjustment Notes</h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-300">{{ $timeEntry->adjustment_reason }}</p>
                </div>
            </div>
        @endif

        @can('adjust', $timeEntry)
            <div class="bg-gray-900 rounded-lg border border-gray-800 lg:col-span-2">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="text-base font-semibold text-white">Adjust Time Entry</h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('time-entries.adjust', $timeEntry) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="clock_in_at" class="block text-sm font-medium text-gray-300 mb-1">Clock In Time</label>
                                <input type="datetime-local" name="clock_in_at" id="clock_in_at"
                                    value="{{ $timeEntry->clock_in_at?->format('Y-m-d\TH:i') }}"
                                    class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
                                @error('clock_in_at')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="clock_out_at" class="block text-sm font-medium text-gray-300 mb-1">Clock Out Time</label>
                                <input type="datetime-local" name="clock_out_at" id="clock_out_at"
                                    value="{{ $timeEntry->clock_out_at?->format('Y-m-d\TH:i') }}"
                                    class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
                                @error('clock_out_at')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="actual_break_minutes" class="block text-sm font-medium text-gray-300 mb-1">Break (minutes)</label>
                                <input type="number" name="actual_break_minutes" id="actual_break_minutes"
                                    value="{{ $timeEntry->actual_break_minutes ?? 0 }}" min="0" max="480"
                                    class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
                                @error('actual_break_minutes')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="adjustment_reason" class="block text-sm font-medium text-gray-300 mb-1">Reason for Adjustment</label>
                            <textarea name="adjustment_reason" id="adjustment_reason" rows="3" required
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm"
                                placeholder="Please provide a reason for this adjustment (min 10 characters)">{{ old('adjustment_reason', $timeEntry->adjustment_reason) }}</textarea>
                            @error('adjustment_reason')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-brand-900 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-brand-800 transition-colors">
                                Save Adjustment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    </div>
</x-layouts.app>

<x-layouts.app title="Time Entries">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">Time Entries</h2>
                    <p class="mt-1 text-sm text-gray-400">Track clock in/out and break times.</p>
                </div>
            </div>
        </div>
    </div>

    @if($settings?->enable_clock_in_out)
        <x-clock-widget />
    @endif

    <div class="bg-gray-900 rounded-lg border border-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800">
            <form method="GET" action="{{ route('time-entries.index') }}" class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <label for="status" class="text-sm text-gray-400">Status:</label>
                    <select name="status" id="status" class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2">
                        <option value="">All</option>
                        @foreach(\App\Enums\TimeEntryStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label for="date_from" class="text-sm text-gray-400">From:</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2">
                </div>

                <div class="flex items-center gap-2">
                    <label for="date_to" class="text-sm text-gray-400">To:</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2">
                </div>

                @if($settings?->require_manager_approval && (auth()->user()->isAdmin() || auth()->user()->isLocationAdmin() || auth()->user()->isDepartmentAdmin()))
                    <label class="flex items-center gap-2 text-sm text-gray-400">
                        <input type="checkbox" name="pending_approval" value="1" {{ request('pending_approval') ? 'checked' : '' }} class="rounded bg-gray-800 border-gray-700">
                        Pending Approval
                    </label>
                @endif

                <button type="submit" class="bg-brand-900 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-brand-800 transition-colors">
                    Filter
                </button>

                @if(request()->hasAny(['status', 'date_from', 'date_to', 'pending_approval']))
                    <a href="{{ route('time-entries.index') }}" class="text-sm text-gray-400 hover:text-white">Clear</a>
                @endif
            </form>
        </div>

        <table class="min-w-full divide-y divide-gray-800">
            <thead>
                <tr class="bg-gray-800/50">
                    <x-table.sortable-header column="clock_in_at" label="Clock In" :currentSort="request('sort', 'clock_in_at')" :currentDirection="request('direction', 'desc')" :isFirst="true" />
                    <x-table.sortable-header column="clock_out_at" label="Clock Out" :currentSort="request('sort', 'clock_in_at')" :currentDirection="request('direction', 'desc')" />
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Employee</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Shift</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Duration</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Variance</th>
                    <x-table.sortable-header column="status" label="Status" :currentSort="request('sort', 'clock_in_at')" :currentDirection="request('direction', 'desc')" />
                    <th class="relative py-3.5 pl-3 pr-6"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($timeEntries as $entry)
                    @include('time-entries._row', ['entry' => $entry])
                @empty
                    <tr>
                        <td colspan="8" class="px-3 py-8 text-center text-sm text-gray-500">No time entries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $timeEntries->links() }}
    </div>
</x-layouts.app>

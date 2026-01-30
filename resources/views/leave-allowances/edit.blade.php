<x-layouts.app title="Edit Leave Allowance">
    <div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Edit Leave Allowance</h2>
            <p class="mt-1 text-sm text-gray-400">Update leave allowance for {{ $leaveAllowance->user->name }}.</p>
        </div>

        <div class="bg-gray-800/50 rounded-lg p-4 mb-6">
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-400">Employee</dt>
                    <dd class="text-white font-medium">{{ $leaveAllowance->user->name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-400">Leave Type</dt>
                    <dd class="text-white font-medium inline-flex items-center gap-2">
                        <span class="inline-block h-3 w-3 rounded-full" style="background-color: {{ $leaveAllowance->leaveType->color }}"></span>
                        {{ $leaveAllowance->leaveType->name }}
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-400">Year</dt>
                    <dd class="text-white font-medium">{{ $leaveAllowance->year }}</dd>
                </div>
                <div>
                    <dt class="text-gray-400">Used Days</dt>
                    <dd class="text-white font-medium">{{ number_format($leaveAllowance->used_days, 1) }}</dd>
                </div>
            </dl>
        </div>

        <form action="{{ route('leave-allowances.update', $leaveAllowance) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <x-form.input name="total_days" label="Total Days" type="number" step="0.5" min="0" max="365" required :value="old('total_days', $leaveAllowance->total_days)" />

            <x-form.input name="carried_over_days" label="Carried Over Days" type="number" step="0.5" min="0" max="365" :value="old('carried_over_days', $leaveAllowance->carried_over_days)" hint="Days carried over from the previous year" />

            <div class="bg-gray-800/50 rounded-lg p-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-400">Remaining Days</span>
                    <span class="text-lg font-semibold {{ $leaveAllowance->remaining_days <= 0 ? 'text-red-400' : 'text-green-400' }}">
                        {{ number_format($leaveAllowance->remaining_days, 1) }}
                    </span>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <x-button variant="secondary" :href="route('leave-allowances.index', ['year' => $leaveAllowance->year])">Cancel</x-button>
                <x-button type="submit">Save Changes</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>

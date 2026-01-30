<x-layouts.app title="Add Leave Allowance">
    <div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Add Leave Allowance</h2>
            <p class="mt-1 text-sm text-gray-400">Assign a leave allowance to an employee for a specific year.</p>
        </div>

        <form action="{{ route('leave-allowances.store') }}" method="POST" class="space-y-5">
            @csrf

            <x-form.select name="user_id" label="Employee" required>
                <option value="">Select an employee</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </x-form.select>

            <x-form.select name="leave_type_id" label="Leave Type" required>
                <option value="">Select a leave type</option>
                @foreach($leaveTypes as $type)
                    <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </x-form.select>

            <x-form.select name="year" label="Year" required>
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ old('year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </x-form.select>

            <x-form.input name="total_days" label="Total Days" type="number" step="0.5" min="0" max="365" required placeholder="e.g., 25" :value="old('total_days', 25)" />

            <x-form.input name="carried_over_days" label="Carried Over Days" type="number" step="0.5" min="0" max="365" placeholder="e.g., 5" :value="old('carried_over_days', 0)" hint="Days carried over from the previous year" />

            <div class="flex justify-end gap-3 pt-4">
                <x-button variant="secondary" :href="route('leave-allowances.index')">Cancel</x-button>
                <x-button type="submit">Create Allowance</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>

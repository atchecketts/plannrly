<x-layouts.app title="Request Leave">
    <div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Request Leave</h2>
            <p class="mt-1 text-sm text-gray-400">Submit a new leave request for approval.</p>
        </div>

        <form action="{{ route('leave-requests.store') }}" method="POST" class="space-y-5">
            @csrf

            <x-form.select name="leave_type_id" label="Leave Type" required placeholder="Select a leave type">
                @foreach($leaveTypes as $type)
                    <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </x-form.select>

            <div class="grid grid-cols-2 gap-4">
                <x-form.input name="start_date" type="date" label="Start Date" required />
                <x-form.input name="end_date" type="date" label="End Date" required />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-form.checkbox name="start_half_day" label="Half day (start)" />
                <x-form.checkbox name="end_half_day" label="Half day (end)" />
            </div>

            <x-form.textarea name="reason" label="Reason (optional)" />

            <div class="flex justify-end gap-3 pt-4">
                <x-button variant="secondary" :href="route('leave-requests.index')">Cancel</x-button>
                <x-button variant="secondary" type="submit" name="submit" value="0">Save as Draft</x-button>
                <x-button type="submit" name="submit" value="1">Submit for Approval</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>

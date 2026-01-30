<x-layouts.app title="Create Staffing Requirement">
    <div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Create Staffing Requirement</h2>
            <p class="mt-1 text-sm text-gray-400">Define minimum and maximum employees needed for a role during a specific time window.</p>
        </div>

        <form action="{{ route('staffing-requirements.store') }}" method="POST" class="space-y-5">
            @csrf

            <x-form.select name="business_role_id" label="Business Role" required placeholder="Select Role">
                @foreach($businessRoles as $role)
                    <option value="{{ $role->id }}" {{ old('business_role_id') == $role->id ? 'selected' : '' }}>
                        {{ $role->name }} ({{ $role->department->name }})
                    </option>
                @endforeach
            </x-form.select>

            <div class="grid grid-cols-2 gap-4">
                <x-form.select name="location_id" label="Location (Optional)" placeholder="All Locations">
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </x-form.select>

                <x-form.select name="department_id" label="Department (Optional)" placeholder="All Departments">
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }} ({{ $department->location->name }})
                        </option>
                    @endforeach
                </x-form.select>
            </div>

            <x-form.select name="day_of_week" label="Day of Week" required>
                @foreach($daysOfWeek as $value => $label)
                    <option value="{{ $value }}" {{ old('day_of_week') === (string)$value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </x-form.select>

            <div class="grid grid-cols-2 gap-4">
                <x-form.input name="start_time" type="time" label="Start Time" required value="{{ old('start_time', '09:00') }}" />
                <x-form.input name="end_time" type="time" label="End Time" required value="{{ old('end_time', '17:00') }}" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-form.input name="min_employees" type="number" label="Minimum Employees" required value="{{ old('min_employees', 1) }}" min="0" />
                    <p class="mt-1 text-xs text-gray-500">Understaffed if below this number</p>
                </div>
                <div>
                    <x-form.input name="max_employees" type="number" label="Maximum Employees (Optional)" value="{{ old('max_employees') }}" min="0" />
                    <p class="mt-1 text-xs text-gray-500">Overstaffed if above this number (leave empty for no limit)</p>
                </div>
            </div>

            <x-form.checkbox name="is_active" label="Active" :checked="old('is_active', true)" />

            <x-form.textarea name="notes" label="Notes (Optional)" />

            <div class="flex justify-end gap-3 pt-4">
                <x-button variant="secondary" :href="route('staffing-requirements.index')">Cancel</x-button>
                <x-button type="submit">Create Requirement</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>

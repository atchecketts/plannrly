<x-layouts.app title="Edit Staffing Requirement">
    <div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Edit Staffing Requirement</h2>
            <p class="mt-1 text-sm text-gray-400">Update the staffing levels for this role and time window.</p>
        </div>

        <form action="{{ route('staffing-requirements.update', $staffingRequirement) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <x-form.select name="business_role_id" label="Business Role" required placeholder="Select Role">
                @foreach($businessRoles as $role)
                    <option value="{{ $role->id }}" {{ old('business_role_id', $staffingRequirement->business_role_id) == $role->id ? 'selected' : '' }}>
                        {{ $role->name }} ({{ $role->department->name }})
                    </option>
                @endforeach
            </x-form.select>

            <div class="grid grid-cols-2 gap-4">
                <x-form.select name="location_id" label="Location (Optional)" placeholder="All Locations">
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ old('location_id', $staffingRequirement->location_id) == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </x-form.select>

                <x-form.select name="department_id" label="Department (Optional)" placeholder="All Departments">
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ old('department_id', $staffingRequirement->department_id) == $department->id ? 'selected' : '' }}>
                            {{ $department->name }} ({{ $department->location->name }})
                        </option>
                    @endforeach
                </x-form.select>
            </div>

            <x-form.select name="day_of_week" label="Day of Week" required>
                @foreach($daysOfWeek as $value => $label)
                    <option value="{{ $value }}" {{ old('day_of_week', $staffingRequirement->day_of_week) == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </x-form.select>

            <div class="grid grid-cols-2 gap-4">
                <x-form.input name="start_time" type="time" label="Start Time" required :value="old('start_time', $staffingRequirement->start_time?->format('H:i'))" />
                <x-form.input name="end_time" type="time" label="End Time" required :value="old('end_time', $staffingRequirement->end_time?->format('H:i'))" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-form.input name="min_employees" type="number" label="Minimum Employees" required :value="old('min_employees', $staffingRequirement->min_employees)" min="0" />
                    <p class="mt-1 text-xs text-gray-500">Understaffed if below this number</p>
                </div>
                <div>
                    <x-form.input name="max_employees" type="number" label="Maximum Employees (Optional)" :value="old('max_employees', $staffingRequirement->max_employees)" min="0" />
                    <p class="mt-1 text-xs text-gray-500">Overstaffed if above this number (leave empty for no limit)</p>
                </div>
            </div>

            <x-form.checkbox name="is_active" label="Active" :checked="old('is_active', $staffingRequirement->is_active)" />

            <x-form.textarea name="notes" label="Notes (Optional)" :value="old('notes', $staffingRequirement->notes)" />

            <div class="flex justify-between pt-4">
                @can('delete', $staffingRequirement)
                    <form action="{{ route('staffing-requirements.destroy', $staffingRequirement) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this requirement?')">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger">Delete</x-button>
                    </form>
                @endcan

                <div class="flex gap-3">
                    <x-button variant="secondary" :href="route('staffing-requirements.index')">Cancel</x-button>
                    <x-button type="submit">Update Requirement</x-button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>

<x-layouts.app title="Edit Business Role">
    <div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Edit Business Role</h2>
            <p class="mt-1 text-sm text-gray-400">Update role details.</p>
        </div>

        <form action="{{ route('business-roles.update', $businessRole) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <x-form.input name="name" label="Role Name" :value="$businessRole->name" required />

            <x-form.select name="department_id" label="Department" required>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ old('department_id', $businessRole->department_id) == $department->id ? 'selected' : '' }}>
                        {{ $department->name }} ({{ $department->location->name }})
                    </option>
                @endforeach
            </x-form.select>

            <x-form.textarea name="description" label="Description (optional)" :value="$businessRole->description" />

            <div>
                <x-form.input name="default_hourly_rate" type="number" label="Default Hourly Rate" :value="$businessRole->default_hourly_rate" step="0.01" />
                <p class="mt-1 text-xs text-gray-500">Used when no employee-specific rate is set</p>
            </div>

            <x-form.color name="color" label="Color" :value="$businessRole->color" />

            <x-form.checkbox name="is_active" label="Active" :checked="$businessRole->is_active" />

            <div class="flex justify-end gap-3 pt-4">
                <x-button variant="secondary" :href="route('business-roles.index')">Cancel</x-button>
                <x-button type="submit">Update Role</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>

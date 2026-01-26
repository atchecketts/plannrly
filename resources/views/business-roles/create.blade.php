<x-layouts.app title="Create Business Role">
    <div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Create Business Role</h2>
            <p class="mt-1 text-sm text-gray-400">Define a new job function for your organization.</p>
        </div>

        <form action="{{ route('business-roles.store') }}" method="POST" class="space-y-5">
            @csrf

            <x-form.input name="name" label="Role Name" required />

            <x-form.select name="department_id" label="Department" required placeholder="Select Department">
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                        {{ $department->name }} ({{ $department->location->name }})
                    </option>
                @endforeach
            </x-form.select>

            <x-form.textarea name="description" label="Description (optional)" />

            <x-form.input name="default_hourly_rate" type="number" label="Default Hourly Rate (optional)" step="0.01" />

            <x-form.color name="color" label="Color" value="#6B7280" />

            <div class="flex justify-end gap-3 pt-4">
                <x-button variant="secondary" :href="route('business-roles.index')">Cancel</x-button>
                <x-button type="submit">Create Role</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>

<x-layouts.app title="Edit Department">
    <div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Edit Department</h2>
            <p class="mt-1 text-sm text-gray-400">Update department details.</p>
        </div>

        <form action="{{ route('departments.update', $department) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <x-form.input name="name" label="Department Name" :value="$department->name" required />

            <x-form.select name="location_id" label="Location" required>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}" {{ old('location_id', $department->location_id) == $location->id ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                @endforeach
            </x-form.select>

            <x-form.textarea name="description" label="Description (optional)" :value="$department->description" />

            <x-form.color name="color" label="Color" :value="$department->color" />

            <x-form.checkbox name="is_active" label="Active" :checked="$department->is_active" />

            <div class="flex justify-end gap-3 pt-4">
                <x-button variant="secondary" :href="route('departments.index')">Cancel</x-button>
                <x-button type="submit">Update Department</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>

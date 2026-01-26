<x-layouts.app title="Add Department">
    <div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Add Department</h2>
            <p class="mt-1 text-sm text-gray-400">Create a new department within a location.</p>
        </div>

        <form action="{{ route('departments.store') }}" method="POST" class="space-y-5">
            @csrf

            <x-form.select name="location_id" label="Location" required placeholder="Select a location">
                @foreach($locations as $location)
                    <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                @endforeach
            </x-form.select>

            <x-form.input name="name" label="Department Name" required />

            <x-form.textarea name="description" label="Description (optional)" />

            <x-form.color name="color" label="Color" />

            <x-form.checkbox name="is_active" label="Active" :checked="true" />

            <div class="flex justify-end gap-3 pt-4">
                <x-button variant="secondary" :href="route('departments.index')">Cancel</x-button>
                <x-button type="submit">Create Department</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>

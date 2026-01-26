<x-layouts.app title="Add Location">
    <div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Add Location</h2>
            <p class="mt-1 text-sm text-gray-400">Create a new location for your organization.</p>
        </div>

        <form action="{{ route('locations.store') }}" method="POST" class="space-y-5">
            @csrf

            <x-form.input name="name" label="Location Name" required />
            <x-form.input name="address_line_1" label="Address Line 1" />
            <x-form.input name="address_line_2" label="Address Line 2" />

            <div class="grid grid-cols-2 gap-4">
                <x-form.input name="city" label="City" />
                <x-form.input name="state" label="State/Province" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-form.input name="postal_code" label="Postal Code" />
                <x-form.input name="country" label="Country" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-form.input name="phone" label="Phone" />
                <x-form.select name="timezone" label="Timezone" required>
                    <option value="UTC" {{ old('timezone', 'UTC') === 'UTC' ? 'selected' : '' }}>UTC</option>
                    <option value="America/New_York" {{ old('timezone') === 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                    <option value="America/Chicago" {{ old('timezone') === 'America/Chicago' ? 'selected' : '' }}>America/Chicago</option>
                    <option value="America/Los_Angeles" {{ old('timezone') === 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles</option>
                    <option value="Europe/London" {{ old('timezone') === 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                    <option value="Europe/Paris" {{ old('timezone') === 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris</option>
                </x-form.select>
            </div>

            <x-form.checkbox name="is_active" label="Active" :checked="true" />

            <div class="flex justify-end gap-3 pt-4">
                <x-button variant="secondary" :href="route('locations.index')">Cancel</x-button>
                <x-button type="submit">Create Location</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>

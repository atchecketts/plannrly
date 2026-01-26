<x-layouts.app title="Edit Location">
    <div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Edit Location</h2>
            <p class="mt-1 text-sm text-gray-400">Update location details.</p>
        </div>

        <form action="{{ route('locations.update', $location) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <x-form.input name="name" label="Location Name" :value="$location->name" required />
            <x-form.input name="address_line_1" label="Address Line 1" :value="$location->address_line_1" />
            <x-form.input name="address_line_2" label="Address Line 2" :value="$location->address_line_2" />

            <div class="grid grid-cols-2 gap-4">
                <x-form.input name="city" label="City" :value="$location->city" />
                <x-form.input name="state" label="State/Province" :value="$location->state" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-form.input name="postal_code" label="Postal Code" :value="$location->postal_code" />
                <x-form.input name="country" label="Country" :value="$location->country" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-form.input name="phone" label="Phone" :value="$location->phone" />
                <x-form.select name="timezone" label="Timezone" required>
                    <option value="UTC" {{ old('timezone', $location->timezone) === 'UTC' ? 'selected' : '' }}>UTC</option>
                    <option value="America/New_York" {{ old('timezone', $location->timezone) === 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                    <option value="America/Chicago" {{ old('timezone', $location->timezone) === 'America/Chicago' ? 'selected' : '' }}>America/Chicago</option>
                    <option value="America/Los_Angeles" {{ old('timezone', $location->timezone) === 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles</option>
                    <option value="Europe/London" {{ old('timezone', $location->timezone) === 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                    <option value="Europe/Paris" {{ old('timezone', $location->timezone) === 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris</option>
                </x-form.select>
            </div>

            <x-form.checkbox name="is_active" label="Active" :checked="$location->is_active" />

            <div class="flex justify-end gap-3 pt-4">
                <x-button variant="secondary" :href="route('locations.show', $location)">Cancel</x-button>
                <x-button type="submit">Save Changes</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>

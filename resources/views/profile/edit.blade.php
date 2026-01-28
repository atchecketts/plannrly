<x-layouts.app title="Edit Profile">
    <div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Edit Profile</h2>
            <p class="mt-1 text-sm text-gray-400">Update your contact information.</p>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <x-form.input name="first_name" label="First Name" :value="$user->first_name" required />
                <x-form.input name="last_name" label="Last Name" :value="$user->last_name" required />
            </div>

            <x-form.input name="email" type="email" label="Email Address" :value="$user->email" required />

            <x-form.input name="phone" label="Phone Number (optional)" :value="$user->phone" />

            <div class="flex justify-end gap-3 pt-4">
                <x-button variant="secondary" :href="route('profile.index')">Cancel</x-button>
                <x-button type="submit">Save Changes</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>

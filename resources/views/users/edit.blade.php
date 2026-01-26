<x-layouts.app title="Edit User">
    <div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Edit User</h2>
            <p class="mt-1 text-sm text-gray-400">Update user details.</p>
        </div>

        <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <x-form.input name="first_name" label="First Name" :value="$user->first_name" required />
                <x-form.input name="last_name" label="Last Name" :value="$user->last_name" required />
            </div>

            <x-form.input name="email" type="email" label="Email" :value="$user->email" required />

            <x-form.input name="phone" label="Phone (optional)" :value="$user->phone" />

            <x-form.input name="password" type="password" label="New Password (leave blank to keep current)" />

            <x-form.input name="password_confirmation" type="password" label="Confirm New Password" />

            <x-form.checkbox name="is_active" label="Active" :checked="$user->is_active" />

            <div class="flex justify-end gap-3 pt-4">
                <x-button variant="secondary" :href="route('users.show', $user)">Cancel</x-button>
                <x-button type="submit">Update User</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>

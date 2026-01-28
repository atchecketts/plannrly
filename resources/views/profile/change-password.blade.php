<x-layouts.app title="Change Password">
    <div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Change Password</h2>
            <p class="mt-1 text-sm text-gray-400">Update your password to keep your account secure.</p>
        </div>

        <form action="{{ route('profile.password.update') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <x-form.input
                name="current_password"
                type="password"
                label="Current Password"
                required
            />

            <x-form.input
                name="password"
                type="password"
                label="New Password"
                required
            />

            <x-form.input
                name="password_confirmation"
                type="password"
                label="Confirm New Password"
                required
            />

            <div class="bg-gray-800/50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-300 mb-2">Password Requirements</h4>
                <ul class="text-sm text-gray-400 space-y-1">
                    <li>At least 8 characters long</li>
                    <li>Contains at least one uppercase letter</li>
                    <li>Contains at least one number</li>
                </ul>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <x-button variant="secondary" :href="route('profile.index')">Cancel</x-button>
                <x-button type="submit">Change Password</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>

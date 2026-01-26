<x-layouts.app title="Create User">
    <div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Create User</h2>
            <p class="mt-1 text-sm text-gray-400">Add a new user to your organization.</p>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <x-form.input name="first_name" label="First Name" required />
                <x-form.input name="last_name" label="Last Name" required />
            </div>

            <x-form.input name="email" type="email" label="Email" required />

            <x-form.input name="phone" label="Phone (optional)" />

            <x-form.input name="password" type="password" label="Password" required />

            <x-form.input name="password_confirmation" type="password" label="Confirm Password" required />

            <div class="flex justify-end gap-3 pt-4">
                <x-button variant="secondary" :href="route('users.index')">Cancel</x-button>
                <x-button type="submit">Create User</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>

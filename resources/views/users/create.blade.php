<x-layouts.app title="Create Employee">
    <div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Create Employee</h2>
            <p class="mt-1 text-sm text-gray-400">Add a new employee to your organization.</p>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-lg bg-red-500/10 border border-red-500/30 p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-red-400">Please fix the following errors:</p>
                        <ul class="mt-2 text-sm text-red-400 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

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
                <x-button type="submit">Create Employee</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>

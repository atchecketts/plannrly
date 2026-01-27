<x-layouts.app title="Edit Tenant">
    <x-slot:header>
        <div class="flex items-center gap-4">
            <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">Edit Tenant</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $tenant->name }}</p>
            </div>
        </div>
    </x-slot:header>

    <div class="max-w-2xl">
        <form action="{{ route('super-admin.tenants.update', $tenant) }}" method="POST" class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Organization Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $tenant->name) }}" required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $tenant->email) }}" required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-300 mb-2">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $tenant->phone) }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-300 mb-2">Address</label>
                    <textarea name="address" id="address" rows="3"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 @error('address') border-red-500 @enderror">{{ old('address', $tenant->address) }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $tenant->is_active) ? 'checked' : '' }}
                        class="w-4 h-4 text-brand-600 bg-gray-800 border-gray-700 rounded focus:ring-brand-500">
                    <label for="is_active" class="text-sm font-medium text-gray-300">Active</label>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-800/50 border-t border-gray-800 flex items-center justify-end gap-3">
                <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="px-4 py-2 text-gray-400 hover:text-white transition-colors">
                    Cancel
                </a>
                <button type="submit" class="bg-brand-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-brand-700 transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>

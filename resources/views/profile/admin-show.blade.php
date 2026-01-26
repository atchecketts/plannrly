<x-layouts.admin-mobile title="Profile" active="profile" :showHeader="false">
    <!-- Status Bar Spacer -->
    <div class="bg-brand-900 h-6"></div>

    <!-- Header -->
    <header class="bg-brand-900 text-white px-4 pb-8 pt-2">
        <div class="flex flex-col items-center">
            <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center text-2xl font-semibold mb-3">
                {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
            </div>
            <h1 class="text-xl font-bold">{{ $user->full_name }}</h1>
            <p class="text-sm text-brand-200 mt-1">{{ $user->getHighestRole()?->label() ?? 'Admin' }}</p>
        </div>
    </header>

    <div class="px-4 -mt-4 space-y-4">
        <!-- Quick Info Card -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-4">
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="text-sm text-white">{{ $user->email }}</p>
                    </div>
                </div>

                @if($user->phone)
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Phone</p>
                            <p class="text-sm text-white">{{ $user->phone }}</p>
                        </div>
                    </div>
                @endif

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Role</p>
                        <p class="text-sm text-white">{{ $user->getHighestRole()?->label() ?? 'Admin' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-800">
                <h2 class="font-semibold text-white">Edit Profile</h2>
            </div>
            <form action="{{ route('profile.update') }}" method="POST" class="p-4 space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="first_name" class="block text-xs font-medium text-gray-400 mb-1">First Name</label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $user->first_name) }}"
                               class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        @error('first_name')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="last_name" class="block text-xs font-medium text-gray-400 mb-1">Last Name</label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}"
                               class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        @error('last_name')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-xs font-medium text-gray-400 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                           class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    @error('email')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-xs font-medium text-gray-400 mb-1">Phone</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-3 border-t border-gray-800">
                    <p class="text-xs text-gray-500 mb-3">Change Password (leave blank to keep current)</p>
                    <div class="space-y-3">
                        <div>
                            <label for="current_password" class="block text-xs font-medium text-gray-400 mb-1">Current Password</label>
                            <input type="password" name="current_password" id="current_password"
                                   class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                            @error('current_password')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="new_password" class="block text-xs font-medium text-gray-400 mb-1">New Password</label>
                            <input type="password" name="new_password" id="new_password"
                                   class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                            @error('new_password')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="new_password_confirmation" class="block text-xs font-medium text-gray-400 mb-1">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                                   class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 bg-brand-900 text-white font-medium rounded-lg hover:bg-brand-800 transition-colors">
                    Save Changes
                </button>
            </form>
        </div>

        <!-- Logout -->
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full py-3 bg-gray-900 border border-gray-800 text-red-400 font-medium rounded-lg hover:bg-gray-800 transition-colors">
                Sign Out
            </button>
        </form>
    </div>

    <div class="h-6"></div>
</x-layouts.admin-mobile>

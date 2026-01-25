<x-layouts.mobile title="Profile" active="profile" :showHeader="false">
    <!-- Header -->
    <header class="bg-brand-600 text-white px-4 pb-8 pt-8">
        <div class="flex flex-col items-center">
            <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center text-2xl font-semibold mb-3">
                {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
            </div>
            <h1 class="text-xl font-bold">{{ $user->full_name }}</h1>
            <p class="text-sm text-brand-200 mt-1">{{ $user->email }}</p>
        </div>
    </header>

    <div class="px-4 -mt-4 space-y-4">
        <!-- Quick Info Card -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-4">
            <div class="space-y-3">
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

                @php
                    $departments = $user->businessRoles->map(fn($role) => $role->department)->filter()->unique('id');
                @endphp
                @if($departments->isNotEmpty())
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Department(s)</p>
                            <p class="text-sm text-white">{{ $departments->pluck('name')->join(', ') }}</p>
                        </div>
                    </div>
                @endif

                @if($user->businessRoles->isNotEmpty())
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Role(s)</p>
                            <p class="text-sm text-white">{{ $user->businessRoles->pluck('name')->join(', ') }}</p>
                        </div>
                    </div>
                @endif
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

                <button type="submit" class="w-full py-3 bg-brand-600 text-white font-medium rounded-lg hover:bg-brand-500 transition-colors">
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
</x-layouts.mobile>

<x-layouts.app title="Notification Preferences">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('notifications.index') }}" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h2 class="text-xl font-semibold text-white">Notification Preferences</h2>
                    <p class="text-sm text-gray-400 mt-1">Choose how you want to receive notifications</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('notifications.preferences.update') }}">
            @csrf
            @method('PUT')

            <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
                <!-- Table Header -->
                <div class="hidden sm:grid grid-cols-3 gap-4 px-6 py-3 bg-gray-850 border-b border-gray-800">
                    <div class="text-sm font-medium text-gray-400">Notification Type</div>
                    <div class="text-sm font-medium text-gray-400 text-center">Email</div>
                    <div class="text-sm font-medium text-gray-400 text-center">In-App</div>
                </div>

                <!-- Notification Types -->
                @foreach($notificationTypes as $type => $config)
                    <div class="grid sm:grid-cols-3 gap-4 px-6 py-4 border-b border-gray-800 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-white">{{ $config['label'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $config['description'] }}</p>
                        </div>
                        <div class="flex items-center justify-start sm:justify-center gap-2">
                            <label class="sm:hidden text-xs text-gray-500">Email:</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="preferences[{{ $type }}][email_enabled]"
                                    value="1"
                                    class="sr-only peer"
                                    {{ $config['email_enabled'] ? 'checked' : '' }}
                                >
                                <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brand-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-start sm:justify-center gap-2">
                            <label class="sm:hidden text-xs text-gray-500">In-App:</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="preferences[{{ $type }}][in_app_enabled]"
                                    value="1"
                                    class="sr-only peer"
                                    {{ $config['in_app_enabled'] ? 'checked' : '' }}
                                >
                                <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brand-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Info Box -->
            <div class="mt-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="text-sm text-blue-300 font-medium">About Notification Channels</p>
                        <ul class="mt-2 text-sm text-blue-200/70 space-y-1">
                            <li><span class="font-medium">Email:</span> Notifications sent to your registered email address</li>
                            <li><span class="font-medium">In-App:</span> Notifications shown in the notification bell and notifications page</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-6 flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand-600 text-white font-medium rounded-lg hover:bg-brand-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Preferences
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>

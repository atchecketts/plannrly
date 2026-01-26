@props(['active' => 'home'])

<nav class="fixed bottom-0 left-0 right-0 bg-gray-900 border-t border-gray-800 safe-area-bottom z-50">
    <div class="grid grid-cols-5 h-16 w-full">
        <a href="{{ route('dashboard') }}"
           class="flex flex-col items-center justify-center {{ $active === 'home' ? 'text-brand-400' : 'text-gray-500' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="text-xs mt-1 {{ $active === 'home' ? 'font-medium' : '' }}">Home</span>
        </a>

        <a href="{{ route('my-shifts.index') }}"
           class="flex flex-col items-center justify-center {{ $active === 'shifts' ? 'text-brand-400' : 'text-gray-500' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-xs mt-1 {{ $active === 'shifts' ? 'font-medium' : '' }}">Shifts</span>
        </a>

        <a href="{{ route('time-clock.index') }}"
           class="flex flex-col items-center justify-center">
            <div class="w-14 h-14 -mt-8 bg-brand-900 rounded-full flex items-center justify-center shadow-lg border-4 border-gray-950">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <span class="text-xs mt-1 text-brand-400 font-medium">Clock</span>
        </a>

        <a href="{{ route('my-swaps.index') }}"
           class="flex flex-col items-center justify-center {{ $active === 'swaps' ? 'text-brand-400' : 'text-gray-500' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            <span class="text-xs mt-1 {{ $active === 'swaps' ? 'font-medium' : '' }}">Swap</span>
        </a>

        <a href="{{ route('profile.show') }}"
           class="flex flex-col items-center justify-center {{ $active === 'profile' ? 'text-brand-400' : 'text-gray-500' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span class="text-xs mt-1 {{ $active === 'profile' ? 'font-medium' : '' }}">Profile</span>
        </a>
    </div>
</nav>

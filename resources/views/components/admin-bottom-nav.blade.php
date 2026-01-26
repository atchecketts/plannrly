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

        <a href="{{ route('schedule.index') }}"
           class="flex flex-col items-center justify-center {{ $active === 'schedule' ? 'text-brand-400' : 'text-gray-500' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-xs mt-1 {{ $active === 'schedule' ? 'font-medium' : '' }}">Schedule</span>
        </a>

        <a href="{{ route('leave-requests.index') }}"
           class="flex flex-col items-center justify-center">
            <div class="w-14 h-14 -mt-8 bg-brand-900 rounded-full flex items-center justify-center shadow-lg border-4 border-gray-950">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <span class="text-xs mt-1 text-brand-400 font-medium">Requests</span>
        </a>

        <a href="{{ route('users.index') }}"
           class="flex flex-col items-center justify-center {{ $active === 'team' ? 'text-brand-400' : 'text-gray-500' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span class="text-xs mt-1 {{ $active === 'team' ? 'font-medium' : '' }}">Team</span>
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

<x-layouts.app title="Notifications">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-semibold text-white">All Notifications</h2>
                <p class="text-sm text-gray-400 mt-1">
                    {{ $unreadCount }} unread notification{{ $unreadCount === 1 ? '' : 's' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-300 bg-gray-800 border border-gray-700 rounded-lg hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Mark All Read
                        </button>
                    </form>
                @endif
                <a href="{{ route('notifications.preferences') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-300 bg-gray-800 border border-gray-700 rounded-lg hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                </a>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
            @forelse($notifications as $notification)
                @php
                    $type = $notification->data['type'] ?? 'notification';
                    $iconColors = [
                        'shift_published' => 'text-blue-400 bg-blue-500/10',
                        'shift_changed' => 'text-amber-400 bg-amber-500/10',
                        'shift_reminder' => 'text-purple-400 bg-purple-500/10',
                        'leave_request_status' => 'text-green-400 bg-green-500/10',
                        'swap_request' => 'text-cyan-400 bg-cyan-500/10',
                        'swap_request_response' => 'text-green-400 bg-green-500/10',
                        'missed_shift' => 'text-red-400 bg-red-500/10',
                    ];
                    $iconColor = $iconColors[$type] ?? 'text-gray-400 bg-gray-500/10';
                    $icons = [
                        'shift_published' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                        'shift_changed' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                        'shift_reminder' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                        'leave_request_status' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                        'swap_request' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
                        'swap_request_response' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                        'missed_shift' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                    ];
                    $icon = $icons[$type] ?? 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9';
                @endphp
                <div class="flex items-start gap-4 p-4 border-b border-gray-800 last:border-0 {{ $notification->read_at ? '' : 'bg-gray-850' }}">
                    <div class="flex-shrink-0 p-2 rounded-lg {{ $iconColor }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm {{ $notification->read_at ? 'text-gray-400' : 'text-white' }}">
                            {{ $notification->data['message'] ?? 'You have a new notification.' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(!$notification->read_at)
                            <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}">
                                @csrf
                                <button type="submit" class="p-1.5 text-gray-500 hover:text-white hover:bg-gray-800 rounded-lg transition-colors" title="Mark as read">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-gray-500 hover:text-red-400 hover:bg-gray-800 rounded-lg transition-colors" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-12">
                    <svg class="w-16 h-16 text-gray-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="text-gray-500 text-lg font-medium">No notifications yet</p>
                    <p class="text-gray-600 text-sm mt-1">We'll let you know when something important happens.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($notifications->hasPages())
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif

        <!-- Clear All -->
        @if($notifications->isNotEmpty())
            <div class="mt-6 flex justify-center">
                <form method="POST" action="{{ route('notifications.clear-all') }}" onsubmit="return confirm('Are you sure you want to delete all notifications? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-400 hover:text-red-300">
                        Clear All Notifications
                    </button>
                </form>
            </div>
        @endif
    </div>
</x-layouts.app>

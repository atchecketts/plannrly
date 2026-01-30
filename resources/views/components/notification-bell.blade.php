<div x-data="{
    open: false,
    notifications: [],
    unreadCount: 0,
    loading: false,
    async fetchNotifications() {
        this.loading = true;
        try {
            const response = await fetch('{{ route('notifications.unread') }}');
            const data = await response.json();
            this.notifications = data.notifications;
            this.unreadCount = data.unread_count;
        } catch (error) {
            console.error('Failed to fetch notifications:', error);
        }
        this.loading = false;
    },
    async markAsRead(id) {
        try {
            await fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Content-Type': 'application/json',
                },
            });
            this.notifications = this.notifications.filter(n => n.id !== id);
            this.unreadCount = Math.max(0, this.unreadCount - 1);
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
        }
    },
    async markAllAsRead() {
        try {
            await fetch('{{ route('notifications.mark-all-read') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Content-Type': 'application/json',
                },
            });
            this.notifications = [];
            this.unreadCount = 0;
        } catch (error) {
            console.error('Failed to mark all notifications as read:', error);
        }
    },
    getNotificationIcon(type) {
        const icons = {
            'shift_published': 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            'shift_changed': 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
            'shift_reminder': 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            'leave_request_status': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'swap_request': 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
            'swap_request_response': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'missed_shift': 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        };
        return icons[type] || 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9';
    },
    getNotificationColor(type) {
        const colors = {
            'shift_published': 'text-blue-400',
            'shift_changed': 'text-amber-400',
            'shift_reminder': 'text-purple-400',
            'leave_request_status': 'text-green-400',
            'swap_request': 'text-cyan-400',
            'swap_request_response': 'text-green-400',
            'missed_shift': 'text-red-400',
        };
        return colors[type] || 'text-gray-400';
    }
}" x-init="fetchNotifications()" @click.away="open = false" class="relative">
    <button
        @click="open = !open; if (open) fetchNotifications()"
        class="relative p-2 text-gray-400 hover:text-white rounded-lg hover:bg-gray-800 transition-colors"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span
            x-show="unreadCount > 0"
            x-text="unreadCount > 99 ? '99+' : unreadCount"
            class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full min-w-[1.25rem]"
        ></span>
    </button>

    <!-- Dropdown -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-2 w-96 bg-gray-800 border border-gray-700 rounded-xl shadow-xl z-50 overflow-hidden"
    >
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-700">
            <h3 class="text-sm font-semibold text-white">Notifications</h3>
            <div class="flex items-center gap-2">
                <button
                    x-show="unreadCount > 0"
                    @click="markAllAsRead()"
                    class="text-xs text-brand-400 hover:text-brand-300"
                >
                    Mark all as read
                </button>
                <a href="{{ route('notifications.index') }}" class="text-xs text-gray-400 hover:text-white">
                    View all
                </a>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
            <template x-if="loading">
                <div class="flex items-center justify-center py-8">
                    <svg class="animate-spin h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </template>

            <template x-if="!loading && notifications.length === 0">
                <div class="flex flex-col items-center justify-center py-8 px-4">
                    <svg class="w-12 h-12 text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="text-sm text-gray-500">No new notifications</p>
                </div>
            </template>

            <template x-for="notification in notifications" :key="notification.id">
                <div class="px-4 py-3 hover:bg-gray-750 border-b border-gray-700/50 last:border-0">
                    <div class="flex items-start gap-3">
                        <div :class="getNotificationColor(notification.type)" class="flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getNotificationIcon(notification.type)" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-200" x-text="notification.message"></p>
                            <p class="text-xs text-gray-500 mt-1" x-text="notification.created_at"></p>
                        </div>
                        <button
                            @click.stop="markAsRead(notification.id)"
                            class="flex-shrink-0 p-1 text-gray-500 hover:text-white rounded"
                            title="Mark as read"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-700 bg-gray-850">
            <a href="{{ route('notifications.preferences') }}" class="flex items-center justify-center gap-2 text-sm text-gray-400 hover:text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Notification Settings
            </a>
        </div>
    </div>
</div>

{{-- Context menu component for shift copy/paste operations --}}
<div x-show="contextMenu.isOpen"
     x-cloak
     @click.outside="contextMenu.isOpen = false"
     @keydown.escape.window="contextMenu.isOpen = false"
     :style="{ left: contextMenu.x + 'px', top: contextMenu.y + 'px' }"
     class="fixed z-50 bg-gray-800 border border-gray-700 rounded-lg shadow-xl py-1 min-w-[160px]"
     x-transition:enter="transition ease-out duration-100"
     x-transition:enter-start="transform opacity-0 scale-95"
     x-transition:enter-end="transform opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-75"
     x-transition:leave-start="transform opacity-100 scale-100"
     x-transition:leave-end="transform opacity-0 scale-95">

    {{-- Shift-specific actions --}}
    <template x-if="contextMenu.type === 'shift'">
        <div>
            <button @click="copyShift(contextMenu.targetId); contextMenu.isOpen = false"
                    class="w-full px-4 py-2 text-left text-sm text-gray-300 hover:bg-gray-700 hover:text-white flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                Copy Shift
                <span class="ml-auto text-xs text-gray-500">Ctrl+C</span>
            </button>
            <button @click="copyDay(contextMenu.targetDate, contextMenu.targetUserId); contextMenu.isOpen = false"
                    class="w-full px-4 py-2 text-left text-sm text-gray-300 hover:bg-gray-700 hover:text-white flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Copy Day
            </button>
            <div class="border-t border-gray-700 my-1"></div>
            <button @click="editModal.open(contextMenu.targetId); contextMenu.isOpen = false"
                    class="w-full px-4 py-2 text-left text-sm text-gray-300 hover:bg-gray-700 hover:text-white flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Shift
            </button>
            <button @click="confirmDeleteShift(contextMenu.targetId); contextMenu.isOpen = false"
                    class="w-full px-4 py-2 text-left text-sm text-red-400 hover:bg-gray-700 hover:text-red-300 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete Shift
            </button>
        </div>
    </template>

    {{-- Empty cell actions --}}
    <template x-if="contextMenu.type === 'cell'">
        <div>
            <template x-if="clipboard.data.length > 0">
                <button @click="paste(contextMenu.targetUserId, contextMenu.targetDate); contextMenu.isOpen = false"
                        class="w-full px-4 py-2 text-left text-sm text-gray-300 hover:bg-gray-700 hover:text-white flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Paste
                    <span class="ml-auto text-xs text-gray-500" x-text="'(' + clipboard.data.length + ')'">(1)</span>
                    <span class="text-xs text-gray-500 ml-1">Ctrl+V</span>
                </button>
            </template>
            <button @click="createShiftFromContext(); contextMenu.isOpen = false"
                    class="w-full px-4 py-2 text-left text-sm text-gray-300 hover:bg-gray-700 hover:text-white flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Create Shift
            </button>
        </div>
    </template>

    {{-- Day header actions --}}
    <template x-if="contextMenu.type === 'day'">
        <div>
            <button @click="copyDay(contextMenu.targetDate); contextMenu.isOpen = false"
                    class="w-full px-4 py-2 text-left text-sm text-gray-300 hover:bg-gray-700 hover:text-white flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Copy Day's Shifts
            </button>
            <template x-if="clipboard.data.length > 0 && clipboard.type === 'day'">
                <button @click="pasteDay(contextMenu.targetDate); contextMenu.isOpen = false"
                        class="w-full px-4 py-2 text-left text-sm text-gray-300 hover:bg-gray-700 hover:text-white flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Paste Day
                    <span class="ml-auto text-xs text-gray-500" x-text="'(' + clipboard.data.length + ' shifts)'">(1 shifts)</span>
                </button>
            </template>
        </div>
    </template>
</div>

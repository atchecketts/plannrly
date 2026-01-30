<x-layouts.app title="Shift Swaps">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">Shift Swap Requests</h2>
                    <p class="mt-1 text-sm text-gray-400">Manage shift swap requests between employees.</p>
                </div>
                @if($myUpcomingShifts->isNotEmpty())
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" type="button"
                                class="inline-flex items-center gap-2 bg-brand-900 text-white py-2 px-4 rounded-lg font-medium hover:bg-brand-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            Request Swap
                            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open"
                             x-cloak
                             @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 bg-gray-800 border border-gray-700 rounded-lg shadow-xl z-50">
                            <div class="px-4 py-3 border-b border-gray-700">
                                <p class="text-sm font-medium text-white">Select a shift to swap</p>
                                <p class="text-xs text-gray-400 mt-0.5">Choose one of your upcoming shifts</p>
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                @foreach($myUpcomingShifts as $shift)
                                    <a href="{{ route('shift-swaps.create', $shift) }}"
                                       class="flex items-center gap-3 px-4 py-3 hover:bg-gray-700/50 transition-colors border-b border-gray-700/50 last:border-0">
                                        @if($shift->businessRole)
                                            <span class="flex-shrink-0 w-3 h-3 rounded-full" style="background-color: {{ $shift->businessRole->color }}"></span>
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-white">{{ $shift->date->format('D, M d') }}</p>
                                            <p class="text-xs text-gray-400">{{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}</p>
                                            @if($shift->businessRole)
                                                <p class="text-xs text-gray-500">{{ $shift->businessRole->name }}</p>
                                            @endif
                                        </div>
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="mb-6 border-b border-gray-800">
        <nav class="-mb-px flex gap-6" aria-label="Tabs">
            <a href="{{ route('shift-swaps.index') }}"
               class="{{ !$status || $status === 'all' ? 'border-brand-500 text-brand-400' : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-700' }} whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors">
                All
                <span class="ml-2 rounded-full bg-gray-800 px-2.5 py-0.5 text-xs font-medium {{ !$status || $status === 'all' ? 'text-brand-400' : 'text-gray-400' }}">{{ $counts['all'] }}</span>
            </a>
            <a href="{{ route('shift-swaps.index', ['status' => 'pending']) }}"
               class="{{ $status === 'pending' ? 'border-brand-500 text-brand-400' : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-700' }} whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors">
                Pending
                <span class="ml-2 rounded-full bg-yellow-500/10 px-2.5 py-0.5 text-xs font-medium text-yellow-400">{{ $counts['pending'] }}</span>
            </a>
            <a href="{{ route('shift-swaps.index', ['status' => 'accepted']) }}"
               class="{{ $status === 'accepted' ? 'border-brand-500 text-brand-400' : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-700' }} whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors">
                Accepted
                <span class="ml-2 rounded-full bg-green-500/10 px-2.5 py-0.5 text-xs font-medium text-green-400">{{ $counts['accepted'] }}</span>
            </a>
            <a href="{{ route('shift-swaps.index', ['status' => 'rejected']) }}"
               class="{{ $status === 'rejected' ? 'border-brand-500 text-brand-400' : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-700' }} whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors">
                Rejected
                <span class="ml-2 rounded-full bg-red-500/10 px-2.5 py-0.5 text-xs font-medium text-red-400">{{ $counts['rejected'] }}</span>
            </a>
        </nav>
    </div>

    <div class="bg-gray-900 rounded-lg border border-gray-800 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-800">
            <thead>
                <tr class="bg-gray-800/50">
                    <th class="py-3.5 pl-6 pr-3 text-left text-sm font-semibold text-gray-300">Requesting Employee</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Their Shift</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Target Employee</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Target Shift</th>
                    <x-table.sortable-header column="status" label="Status" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" />
                    <x-table.sortable-header column="requested" label="Requested" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" />
                    <th class="relative py-3.5 pl-3 pr-6"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800" x-data="{
                expandedGroups: {},
                toggleGroup(key) {
                    this.expandedGroups[key] = !this.expandedGroups[key];
                },
                isExpanded(key) {
                    return this.expandedGroups[key] === true;
                }
            }">
                @if($sortParams['group'] && !empty($allGroups))
                    @foreach($allGroups as $group)
                        @php
                            $groupKey = $group['key'];
                            $groupLabel = $group['label'];
                            $groupSwaps = $swapRequests->filter(function($swap) use ($sortParams, $groupKey) {
                                return match($sortParams['group']) {
                                    'status' => $groupKey === 'status-' . $swap->status->value,
                                    'requested' => $groupKey === 'requested-' . $swap->created_at->format('Y-m-d'),
                                    default => false,
                                };
                            });
                        @endphp
                        <x-table.group-header :label="$groupLabel" :groupKey="$groupKey" :colspan="7" :count="$groupSwaps->count()" />
                        @foreach($groupSwaps as $swap)
                            @include('shift-swaps._row', ['swap' => $swap, 'groupKey' => $groupKey])
                        @endforeach
                    @endforeach
                @else
                    @forelse($swapRequests as $swap)
                        @include('shift-swaps._row', ['swap' => $swap, 'groupKey' => null])
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                @if($status && $status !== 'all')
                                    <p class="text-gray-400 font-medium">No {{ $status }} swap requests</p>
                                    <p class="text-sm text-gray-500 mt-1">There are no swap requests with this status.</p>
                                @else
                                    <p class="text-gray-400 font-medium">No swap requests yet</p>
                                    @if($myUpcomingShifts->isNotEmpty())
                                        <p class="text-sm text-gray-500 mt-1">Use the "Request Swap" button above to swap one of your shifts.</p>
                                    @else
                                        <p class="text-sm text-gray-500 mt-1">You don't have any upcoming shifts to swap.</p>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
                @endif
            </tbody>
        </table>
    </div>

    @if(!$sortParams['group'])
        <div class="mt-4">
            {{ $swapRequests->links() }}
        </div>
    @endif
</x-layouts.app>

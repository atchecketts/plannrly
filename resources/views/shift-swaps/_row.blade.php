<tr class="hover:bg-gray-800/50 transition-colors"
    @if($groupKey) x-show="isExpanded('{{ $groupKey }}')" x-cloak @endif>
    <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm font-medium text-white">
        {{ $swap->requestingUser->full_name }}
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">
        <div class="flex items-center gap-2">
            @if($swap->requestingShift->businessRole)
                <span class="inline-block h-2.5 w-2.5 rounded-full" style="background-color: {{ $swap->requestingShift->businessRole->color }}"></span>
            @endif
            <span>{{ $swap->requestingShift->date->format('M d') }}</span>
            <span class="text-gray-600">{{ $swap->requestingShift->start_time->format('H:i') }} - {{ $swap->requestingShift->end_time->format('H:i') }}</span>
        </div>
        @if($swap->requestingShift->department)
            <div class="text-xs text-gray-500 mt-0.5">{{ $swap->requestingShift->department->name }}</div>
        @endif
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-white">
        {{ $swap->targetUser->full_name }}
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">
        @if($swap->targetShift)
            <div class="flex items-center gap-2">
                @if($swap->targetShift->businessRole)
                    <span class="inline-block h-2.5 w-2.5 rounded-full" style="background-color: {{ $swap->targetShift->businessRole->color }}"></span>
                @endif
                <span>{{ $swap->targetShift->date->format('M d') }}</span>
                <span class="text-gray-600">{{ $swap->targetShift->start_time->format('H:i') }} - {{ $swap->targetShift->end_time->format('H:i') }}</span>
            </div>
        @else
            <span class="text-gray-600 italic">Take over only</span>
        @endif
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm">
        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
            {{ $swap->status->color() === 'gray' ? 'bg-gray-500/10 text-gray-400 ring-gray-500/20' : '' }}
            {{ $swap->status->color() === 'yellow' ? 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/20' : '' }}
            {{ $swap->status->color() === 'green' ? 'bg-green-500/10 text-green-400 ring-green-500/20' : '' }}
            {{ $swap->status->color() === 'red' ? 'bg-red-500/10 text-red-400 ring-red-500/20' : '' }}">
            {{ $swap->status->label() }}
        </span>
        @if($swap->status === \App\Enums\SwapRequestStatus::Accepted && !$swap->approved_by && (auth()->user()->tenant?->tenantSettings?->require_admin_approval_for_swaps ?? true))
            <span class="ml-1 inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-amber-500/10 text-amber-400 ring-1 ring-inset ring-amber-500/20">
                Awaiting Admin
            </span>
        @endif
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">
        {{ $swap->created_at->diffForHumans() }}
    </td>
    <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
        <div class="flex items-center justify-end gap-2">
            @can('respond', $swap)
                <form action="{{ route('shift-swaps.accept', $swap) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-green-400 hover:text-green-300">Accept</button>
                </form>
                <form action="{{ route('shift-swaps.reject', $swap) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-red-400 hover:text-red-300">Reject</button>
                </form>
            @endcan
            @can('cancel', $swap)
                <form action="{{ route('shift-swaps.cancel', $swap) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-gray-300">Cancel</button>
                </form>
            @endcan
            @can('adminApprove', $swap)
                <form action="{{ route('shift-swaps.approve', $swap) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-brand-400 hover:text-brand-300">Approve Swap</button>
                </form>
            @endcan
        </div>
    </td>
</tr>

<tr class="hover:bg-gray-800/50 transition-colors">
    <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm text-white">
        @if($entry->clock_in_at)
            {{ $entry->clock_in_at->format('M d, Y g:i A') }}
        @else
            <span class="text-gray-500">-</span>
        @endif
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">
        @if($entry->clock_out_at)
            {{ $entry->clock_out_at->format('g:i A') }}
        @else
            <span class="text-gray-500">-</span>
        @endif
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm text-white">
        {{ $entry->user->full_name }}
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">
        @if($entry->shift)
            <div class="flex items-center gap-2">
                @if($entry->shift->businessRole)
                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $entry->shift->businessRole->color }}"></span>
                    {{ $entry->shift->businessRole->name }}
                @endif
            </div>
            <p class="text-xs text-gray-500">
                {{ $entry->shift->start_time->format('g:i A') }} - {{ $entry->shift->end_time->format('g:i A') }}
            </p>
        @else
            <span class="text-gray-500">-</span>
        @endif
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm">
        @if($entry->total_worked_hours !== null)
            <span class="text-white font-medium">{{ $entry->total_worked_hours }}h</span>
            @if($entry->actual_break_minutes)
                <span class="text-gray-500">({{ $entry->actual_break_minutes }}m break)</span>
            @endif
        @else
            <span class="text-gray-500">-</span>
        @endif
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm">
        @if($entry->shift && $entry->clock_in_at)
            @php $clockInStatus = $entry->clock_in_status; @endphp
            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                {{ $clockInStatus['color'] === 'green' ? 'bg-green-500/10 text-green-400 ring-green-500/20' : '' }}
                {{ $clockInStatus['color'] === 'yellow' ? 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/20' : '' }}
                {{ $clockInStatus['color'] === 'red' ? 'bg-red-500/10 text-red-400 ring-red-500/20' : '' }}
                {{ $clockInStatus['color'] === 'blue' ? 'bg-blue-500/10 text-blue-400 ring-blue-500/20' : '' }}
                {{ $clockInStatus['color'] === 'gray' ? 'bg-gray-500/10 text-gray-400 ring-gray-500/20' : '' }}">
                {{ $clockInStatus['label'] }}
            </span>
            @if($entry->variance_minutes !== null)
                <span class="ml-1 text-xs text-gray-500">
                    ({{ $entry->formatVariance($entry->variance_minutes) }})
                </span>
            @endif
        @else
            <span class="text-gray-500">-</span>
        @endif
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm">
        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
            {{ $entry->status->color() === 'green' ? 'bg-green-500/10 text-green-400 ring-green-500/20' : '' }}
            {{ $entry->status->color() === 'yellow' ? 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/20' : '' }}
            {{ $entry->status->color() === 'gray' ? 'bg-gray-500/10 text-gray-400 ring-gray-500/20' : '' }}">
            {{ $entry->status->label() }}
        </span>
        @if($entry->isClockedOut() && !$entry->isApproved() && $entry->requiresApproval())
            <span class="ml-1 inline-flex items-center rounded-md bg-amber-500/10 px-2 py-1 text-xs font-medium text-amber-400 ring-1 ring-inset ring-amber-500/20">
                Pending Approval
            </span>
        @elseif($entry->isApproved())
            <span class="ml-1 inline-flex items-center rounded-md bg-blue-500/10 px-2 py-1 text-xs font-medium text-blue-400 ring-1 ring-inset ring-blue-500/20">
                Approved
            </span>
        @endif
    </td>
    <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
        <a href="{{ route('time-entries.show', $entry) }}" class="text-brand-400 hover:text-brand-300">View</a>
    </td>
</tr>

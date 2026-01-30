<tr class="hover:bg-gray-800/50 transition-colors"
    @if($groupKey) x-show="isExpanded('{{ $groupKey }}')" x-cloak @endif>
    @if(auth()->user()->isAdmin() || auth()->user()->isLocationAdmin() || auth()->user()->isDepartmentAdmin())
        <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm font-medium text-white">
            {{ $leaveRequest->user->full_name }}
        </td>
    @endif
    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">
        <span class="inline-flex items-center gap-2">
            <span class="inline-block h-3 w-3 rounded-full" style="background-color: {{ $leaveRequest->leaveType->color }}"></span>
            {{ $leaveRequest->leaveType->name }}
        </span>
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">
        {{ $leaveRequest->start_date->format('M d') }} - {{ $leaveRequest->end_date->format('M d, Y') }}
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">{{ $leaveRequest->total_days }}</td>
    <td class="whitespace-nowrap px-3 py-4 text-sm">
        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
            {{ $leaveRequest->status->color() === 'gray' ? 'bg-gray-500/10 text-gray-400 ring-gray-500/20' : '' }}
            {{ $leaveRequest->status->color() === 'yellow' ? 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/20' : '' }}
            {{ $leaveRequest->status->color() === 'green' ? 'bg-green-500/10 text-green-400 ring-green-500/20' : '' }}
            {{ $leaveRequest->status->color() === 'red' ? 'bg-red-500/10 text-red-400 ring-red-500/20' : '' }}">
            {{ $leaveRequest->status->label() }}
        </span>
    </td>
    <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
        <a href="{{ route('leave-requests.show', $leaveRequest) }}" class="text-brand-400 hover:text-brand-300">View</a>
        @can('review', $leaveRequest)
            <form action="{{ route('leave-requests.review', $leaveRequest) }}" method="POST" class="inline ml-4">
                @csrf
                <input type="hidden" name="action" value="approve">
                <button type="submit" class="text-green-400 hover:text-green-300">Approve</button>
            </form>
            <form action="{{ route('leave-requests.review', $leaveRequest) }}" method="POST" class="inline ml-2">
                @csrf
                <input type="hidden" name="action" value="reject">
                <button type="submit" class="text-red-400 hover:text-red-300">Reject</button>
            </form>
        @endcan
    </td>
</tr>

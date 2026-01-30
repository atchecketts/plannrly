<tr class="hover:bg-gray-800/50 transition-colors"
    @if($groupKey) x-show="isExpanded('{{ $groupKey }}')" x-cloak @endif>
    <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm font-medium text-white">
        <span class="inline-flex items-center gap-2">
            <span class="inline-block h-3 w-3 rounded-full" style="background-color: {{ $leaveType->color }}"></span>
            {{ $leaveType->name }}
        </span>
    </td>
    <td class="px-3 py-4 text-sm text-gray-400">
        <div class="flex flex-wrap gap-1.5">
            @if($leaveType->requires_approval)
                <span class="inline-flex items-center rounded-md bg-amber-500/10 px-2 py-0.5 text-xs font-medium text-amber-400 ring-1 ring-inset ring-amber-500/20">Approval Required</span>
            @endif
            @if($leaveType->affects_allowance)
                <span class="inline-flex items-center rounded-md bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-400 ring-1 ring-inset ring-blue-500/20">Uses Allowance</span>
            @endif
            @if($leaveType->is_paid)
                <span class="inline-flex items-center rounded-md bg-green-500/10 px-2 py-0.5 text-xs font-medium text-green-400 ring-1 ring-inset ring-green-500/20">Paid</span>
            @else
                <span class="inline-flex items-center rounded-md bg-gray-500/10 px-2 py-0.5 text-xs font-medium text-gray-400 ring-1 ring-inset ring-gray-500/20">Unpaid</span>
            @endif
        </div>
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">{{ $leaveType->leave_requests_count }}</td>
    <td class="whitespace-nowrap px-3 py-4 text-sm">
        @if($leaveType->is_active)
            <span class="inline-flex items-center rounded-md bg-green-500/10 px-2 py-1 text-xs font-medium text-green-400 ring-1 ring-inset ring-green-500/20">Active</span>
        @else
            <span class="inline-flex items-center rounded-md bg-red-500/10 px-2 py-1 text-xs font-medium text-red-400 ring-1 ring-inset ring-red-500/20">Inactive</span>
        @endif
    </td>
    <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
        <div class="flex items-center justify-end gap-3">
            @can('update', $leaveType)
                <a href="{{ route('leave-types.edit', $leaveType) }}" class="text-brand-400 hover:text-brand-300">Edit</a>
            @endcan
            @can('delete', $leaveType)
                @if($leaveType->leave_requests_count === 0)
                    <form action="{{ route('leave-types.destroy', $leaveType) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this leave type?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                    </form>
                @endif
            @endcan
        </div>
    </td>
</tr>

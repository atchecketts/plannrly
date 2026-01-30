<tr class="hover:bg-gray-800/50 transition-colors"
    @if($groupKey) x-show="isExpanded('{{ $groupKey }}')" x-cloak @endif>
    <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm font-medium text-white">
        {{ $allowance->user->name }}
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">
        <span class="inline-flex items-center gap-2">
            <span class="inline-block h-3 w-3 rounded-full" style="background-color: {{ $allowance->leaveType->color }}"></span>
            {{ $allowance->leaveType->name }}
        </span>
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400 text-right">{{ number_format($allowance->total_days, 1) }}</td>
    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400 text-right">{{ number_format($allowance->used_days, 1) }}</td>
    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400 text-right">{{ number_format($allowance->carried_over_days, 1) }}</td>
    <td class="whitespace-nowrap px-3 py-4 text-sm text-right">
        @php $remaining = $allowance->remaining_days; @endphp
        <span class="{{ $remaining <= 0 ? 'text-red-400' : ($remaining <= 5 ? 'text-amber-400' : 'text-green-400') }}">
            {{ number_format($remaining, 1) }}
        </span>
    </td>
    <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
        <div class="flex items-center justify-end gap-3">
            @can('update', $allowance)
                <a href="{{ route('leave-allowances.edit', $allowance) }}" class="text-brand-400 hover:text-brand-300">Edit</a>
            @endcan
            @can('delete', $allowance)
                @if($allowance->used_days == 0)
                    <form action="{{ route('leave-allowances.destroy', $allowance) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this allowance?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                    </form>
                @endif
            @endcan
        </div>
    </td>
</tr>

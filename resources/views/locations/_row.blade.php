<tr class="hover:bg-gray-800/50 transition-colors"
    @if($groupKey) x-show="isExpanded('{{ $groupKey }}')" x-cloak @endif>
    <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm font-medium text-white">
        {{ $location->name }}
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">{{ $location->city }}</td>
    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">{{ $location->departments_count }}</td>
    <td class="whitespace-nowrap px-3 py-4 text-sm">
        @if($location->is_active)
            <span class="inline-flex items-center rounded-md bg-green-500/10 px-2 py-1 text-xs font-medium text-green-400 ring-1 ring-inset ring-green-500/20">Active</span>
        @else
            <span class="inline-flex items-center rounded-md bg-red-500/10 px-2 py-1 text-xs font-medium text-red-400 ring-1 ring-inset ring-red-500/20">Inactive</span>
        @endif
    </td>
    <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
        <a href="{{ route('locations.show', $location) }}" class="text-brand-400 hover:text-brand-300">View</a>
        @can('update', $location)
            <a href="{{ route('locations.edit', $location) }}" class="ml-4 text-brand-400 hover:text-brand-300">Edit</a>
        @endcan
    </td>
</tr>

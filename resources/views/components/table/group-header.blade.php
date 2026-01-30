@props([
    'label',
    'groupKey',
    'count' => null,
    'colspan' => 1,
])

<tr
    class="bg-gray-800/50 cursor-pointer hover:bg-gray-700/50 transition-colors"
    @click="toggleGroup('{{ $groupKey }}')"
>
    <td colspan="{{ $colspan }}" class="py-3 pl-6 pr-3">
        <div class="flex items-center gap-2">
            <svg
                class="h-4 w-4 text-gray-400 transition-transform duration-200"
                :class="isExpanded('{{ $groupKey }}') ? 'rotate-90' : ''"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-sm font-semibold text-white">{{ $label }}</span>
            @if($count !== null)
                <span class="text-xs text-gray-500 bg-gray-700/50 px-2 py-0.5 rounded-full">{{ $count }}</span>
            @endif
        </div>
    </td>
</tr>

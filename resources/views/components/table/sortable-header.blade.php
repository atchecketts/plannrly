@props([
    'column',
    'label',
    'currentSort' => null,
    'currentDirection' => 'asc',
    'currentGroup' => null,
    'groupable' => false,
    'align' => 'left',
    'isFirst' => false,
])

@php
    $isActive = $currentSort === $column;
    $isGrouped = $currentGroup === $column;

    // Build sort URL: cycle none -> asc -> desc -> none
    $params = request()->query();
    unset($params['page']);

    if ($isActive && $currentDirection === 'asc') {
        $sortParams = array_merge($params, ['sort' => $column, 'direction' => 'desc']);
    } elseif ($isActive && $currentDirection === 'desc') {
        $sortParams = $params;
        unset($sortParams['sort'], $sortParams['direction']);
    } else {
        $sortParams = array_merge($params, ['sort' => $column, 'direction' => 'asc']);
    }

    // Build group URL: toggle on/off
    $groupParams = $params;
    unset($groupParams['page']);
    if ($isGrouped) {
        unset($groupParams['group']);
    } else {
        $groupParams['group'] = $column;
    }

    $sortUrl = request()->url() . '?' . http_build_query($sortParams);
    $groupUrl = request()->url() . '?' . http_build_query($groupParams);

    $thClass = $isFirst
        ? 'py-3.5 pl-6 pr-3'
        : 'px-3 py-3.5';

    $alignClass = $align === 'right' ? 'text-right' : 'text-left';
@endphp

<th class="{{ $thClass }} {{ $alignClass }} text-sm font-semibold text-gray-300">
    <div class="inline-flex items-center gap-1.5 {{ $align === 'right' ? 'flex-row-reverse' : '' }}">
        <a href="{{ $sortUrl }}" class="group inline-flex items-center gap-1 hover:text-white transition-colors {{ $isActive ? 'text-white' : '' }}">
            <span>{{ $label }}</span>
            <span class="flex-none">
                @if($isActive && $currentDirection === 'asc')
                    <svg class="h-4 w-4 text-brand-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                @elseif($isActive && $currentDirection === 'desc')
                    <svg class="h-4 w-4 text-brand-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                @else
                    <svg class="h-4 w-4 text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z" clip-rule="evenodd" />
                    </svg>
                @endif
            </span>
        </a>

        @if($groupable)
            <a href="{{ $groupUrl }}" class="ml-1 p-1 rounded hover:bg-gray-700 transition-colors {{ $isGrouped ? 'text-brand-400 bg-brand-400/10' : 'text-gray-400 hover:text-white' }}" title="{{ $isGrouped ? 'Remove grouping' : 'Group by ' . $label }}">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm2 4A.75.75 0 014.75 8h10.5a.75.75 0 010 1.5H4.75A.75.75 0 014 8.75zm2 4A.75.75 0 016.75 12h6.5a.75.75 0 010 1.5h-6.5A.75.75 0 016 12.75z" clip-rule="evenodd" />
                </svg>
            </a>
        @endif
    </div>
</th>

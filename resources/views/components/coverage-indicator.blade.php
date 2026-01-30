@props([
    'status',
    'scheduled' => 0,
    'min' => 0,
    'max' => null,
    'showTooltip' => true,
    'size' => 'sm', // sm, md, lg
])

@php
    use App\Enums\CoverageStatus;

    $statusEnum = $status instanceof CoverageStatus ? $status : CoverageStatus::tryFrom($status) ?? CoverageStatus::NoRequirement;

    $sizeClasses = match($size) {
        'lg' => 'px-3 py-1.5 text-sm',
        'md' => 'px-2.5 py-1 text-xs',
        default => 'px-2 py-0.5 text-xs',
    };

    $iconSize = match($size) {
        'lg' => 'w-4 h-4',
        'md' => 'w-3.5 h-3.5',
        default => 'w-3 h-3',
    };

    $tooltipText = match($statusEnum) {
        CoverageStatus::Understaffed => "Understaffed: {$scheduled}/{$min} employees (need " . ($min - $scheduled) . " more)",
        CoverageStatus::Overstaffed => "Overstaffed: {$scheduled}/{$max} max (" . ($scheduled - $max) . " over)",
        CoverageStatus::Adequate => "Adequate: {$scheduled} employees" . ($max ? " (max {$max})" : ""),
        CoverageStatus::NoRequirement => "No staffing requirement defined",
    };
@endphp

<span
    {{ $attributes->merge(['class' => "inline-flex items-center gap-1 rounded-md font-medium ring-1 ring-inset {$sizeClasses} {$statusEnum->colorClasses()}"]) }}
    @if($showTooltip) title="{{ $tooltipText }}" @endif
>
    <svg class="{{ $iconSize }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        {!! $statusEnum->iconSvg() !!}
    </svg>
    @if($statusEnum === CoverageStatus::Understaffed)
        <span>{{ $scheduled }}/{{ $min }}</span>
    @elseif($statusEnum === CoverageStatus::Overstaffed)
        <span>{{ $scheduled }}/{{ $max }}</span>
    @elseif($statusEnum === CoverageStatus::Adequate)
        <span>{{ $scheduled }}</span>
    @else
        <span>-</span>
    @endif
</span>

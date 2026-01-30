@props([
    'summary', // array with understaffed, overstaffed, adequate, total keys
    'showDetails' => true,
])

@php
    use App\Enums\CoverageStatus;

    $understaffed = $summary['understaffed'] ?? 0;
    $overstaffed = $summary['overstaffed'] ?? 0;
    $adequate = $summary['adequate'] ?? 0;
    $total = $summary['total'] ?? 0;

    $hasIssues = $understaffed > 0 || $overstaffed > 0;
@endphp

<div {{ $attributes->merge(['class' => 'bg-gray-800/50 rounded-lg border border-gray-700 p-4']) }}>
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-medium text-gray-300">Coverage Status</h3>
        @if($hasIssues)
            <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                {{ $understaffed + $overstaffed }} {{ str('issue')->plural($understaffed + $overstaffed) }}
            </span>
        @else
            <span class="inline-flex items-center gap-1 text-xs font-medium text-green-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                All covered
            </span>
        @endif
    </div>

    @if($showDetails && $total > 0)
        <div class="grid grid-cols-3 gap-3">
            @if($understaffed > 0)
                <div class="text-center p-2 rounded bg-red-500/10 border border-red-500/20">
                    <div class="text-lg font-bold text-red-400">{{ $understaffed }}</div>
                    <div class="text-xs text-red-400/70">Understaffed</div>
                </div>
            @endif

            @if($overstaffed > 0)
                <div class="text-center p-2 rounded bg-yellow-500/10 border border-yellow-500/20">
                    <div class="text-lg font-bold text-yellow-400">{{ $overstaffed }}</div>
                    <div class="text-xs text-yellow-400/70">Overstaffed</div>
                </div>
            @endif

            @if($adequate > 0)
                <div class="text-center p-2 rounded bg-green-500/10 border border-green-500/20">
                    <div class="text-lg font-bold text-green-400">{{ $adequate }}</div>
                    <div class="text-xs text-green-400/70">Adequate</div>
                </div>
            @endif
        </div>

        @if($total > 0)
            <div class="mt-3 pt-3 border-t border-gray-700">
                <div class="h-2 rounded-full bg-gray-700 overflow-hidden flex">
                    @if($adequate > 0)
                        <div class="h-full bg-green-500" style="width: {{ ($adequate / $total) * 100 }}%"></div>
                    @endif
                    @if($overstaffed > 0)
                        <div class="h-full bg-yellow-500" style="width: {{ ($overstaffed / $total) * 100 }}%"></div>
                    @endif
                    @if($understaffed > 0)
                        <div class="h-full bg-red-500" style="width: {{ ($understaffed / $total) * 100 }}%"></div>
                    @endif
                </div>
                <p class="mt-1 text-xs text-gray-500 text-center">{{ $total }} {{ str('requirement')->plural($total) }} tracked</p>
            </div>
        @endif
    @elseif($total === 0)
        <p class="text-sm text-gray-500 text-center py-2">No staffing requirements defined for this period.</p>
    @endif
</div>

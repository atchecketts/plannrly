@props(['class' => 'h-10'])

<div {{ $attributes->merge(['class' => 'flex items-center gap-2 ' . $class]) }}>
    {{-- Try image first, fall back to styled text --}}
    <img
        src="/plannrly.png"
        alt="Plannrly"
        class="h-full w-auto"
        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
    >
    <div class="items-center gap-2 hidden">
        <div class="bg-brand-900 rounded-lg p-1.5 flex items-center justify-center" style="height: 100%; aspect-ratio: 1;">
            <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
        <span class="font-bold text-white text-xl tracking-tight">Plannrly</span>
    </div>
</div>

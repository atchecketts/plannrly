@props([
    'variant' => 'primary',
    'type' => 'submit',
    'href' => null,
])

@php
    $baseClasses = 'py-3 px-4 rounded-lg font-medium transition-colors';
    $variantClasses = match($variant) {
        'primary' => 'bg-brand-900 text-white hover:bg-brand-800 focus:ring-4 focus:ring-brand-500/25',
        'secondary' => 'border border-gray-700 text-gray-300 hover:bg-gray-800',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-4 focus:ring-red-500/25',
        'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-4 focus:ring-green-500/25',
        default => 'bg-brand-900 text-white hover:bg-brand-800 focus:ring-4 focus:ring-brand-500/25',
    };
    $classes = $baseClasses . ' ' . $variantClasses;
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif

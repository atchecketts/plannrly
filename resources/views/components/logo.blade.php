@props(['class' => 'h-10'])

<div {{ $attributes->merge(['class' => 'flex items-center']) }}>
    <img src="{{ asset('Plannrly.png') }}" alt="Plannrly" class="{{ $class }}">
</div>

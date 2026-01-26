@props([
    'name',
    'label' => null,
    'value' => '#3B82F6',
])

<div>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-300 mb-1.5">{{ $label }}</label>
    @endif
    <input
        type="color"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        {{ $attributes->merge(['class' => 'h-12 w-20 bg-gray-800 border border-gray-700 rounded-lg cursor-pointer']) }}
    >
    @error($name)
        <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
    @enderror
</div>

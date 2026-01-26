@props([
    'name',
    'label',
    'checked' => false,
    'value' => '1',
])

<div class="flex items-center">
    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ $value }}"
        @if(old($name, $checked)) checked @endif
        {{ $attributes->merge(['class' => 'w-4 h-4 text-brand-600 bg-gray-800 border-gray-600 rounded focus:ring-brand-500']) }}
    >
    <label for="{{ $name }}" class="ml-2 text-sm text-gray-400">{{ $label }}</label>
</div>

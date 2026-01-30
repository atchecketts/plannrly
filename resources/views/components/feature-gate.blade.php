@props(['feature', 'fallback' => null])

@php
    $featureEnum = \App\Enums\FeatureAddon::tryFrom($feature);
    $hasFeature = $featureEnum && auth()->user()?->tenant?->hasFeature($featureEnum);
@endphp

@if($hasFeature)
    {{ $slot }}
@elseif($fallback)
    {{ $fallback }}
@endif

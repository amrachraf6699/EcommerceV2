@props([
    'color',
    'class' => '',
])

@php($hex = \App\Models\ProductVariant::normalizeHexColor($color))

@if ($hex)
    <span {{ $attributes->merge(['class' => 'frontend-color-swatch ' . $class]) }} style="--variant-color: {{ $hex }}" role="img" aria-label="{{ $hex }}" title="{{ $hex }}"></span>
@endif

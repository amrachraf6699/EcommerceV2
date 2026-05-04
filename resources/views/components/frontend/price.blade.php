@props([
    'amount' => null,
    'currency' => 'BHD',
    'compareAmount' => null,
    'wrapperClass' => '',
    'rowClass' => '',
    'amountClass' => '',
    'compareClass' => '',
    'secondaryClass' => '',
    'noteClass' => '',
    'unavailableClass' => '',
    'unavailableText' => null,
])

@php
    $currency = strtoupper($currency);
    $unavailableText ??= __('storefront.common.price_unavailable');
@endphp

@if ($amount !== null)
    <div
        class="price-display {{ $wrapperClass }}"
        data-price-root
        data-bhd-amount="{{ number_format((float) $amount, 2, '.', '') }}"
        data-bhd-currency="{{ $currency }}"
        @if ($compareAmount !== null)
            data-bhd-compare-amount="{{ number_format((float) $compareAmount, 2, '.', '') }}"
        @endif
    >
        <div class="{{ $rowClass }}">
            <span class="{{ $amountClass }}" data-bhd-primary>{{ storefront_format_money($amount, $currency) }}</span>
            @if ($compareAmount !== null)
                <span class="{{ $compareClass }}" data-bhd-compare>{{ storefront_format_money($compareAmount, $currency) }}</span>
            @endif
        </div>
    </div>
@else
    <span class="{{ $unavailableClass }}">{{ $unavailableText }}</span>
@endif

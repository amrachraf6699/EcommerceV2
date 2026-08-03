@php($title = __('storefront.checkout'))

@extends('frontend.layouts.app')

@section('content')
<section class="px-6 pb-20" style="padding-top:120px">
  <div class="max-w-3xl mx-auto border p-8 md:p-10" style="border-color:var(--line-soft);background:var(--gray-dark)">
    <div class="mb-8">
      <div class="divider reveal"></div>
      <h1 class="text-3xl md:text-4xl font-black mb-3">{{ __('storefront.checkout') }}</h1>
      <p style="color:var(--gray-light)">{{ __('storefront.checkout_copy') }}</p>
    </div>

    <form action="{{ route('storefront.checkout.result', ['locale' => app()->getLocale(), 'order' => $order->order_number]) }}" class="paymentWidgets" data-brands="{{ implode(' ', $brands) }}"></form>
  </div>
</section>
@endsection

@push('scripts')
<script src="{{ $widgetUrl }}" @if($widgetIntegrity) integrity="{{ $widgetIntegrity }}" crossorigin="anonymous" @endif></script>
@endpush

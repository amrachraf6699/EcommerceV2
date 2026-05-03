@php($title = __('storefront.checkout'))

@extends('frontend.layouts.app')

@section('content')
<section class="px-6 pb-20" style="padding-top:120px">
  <div class="max-w-4xl mx-auto border p-8 md:p-10" style="border-color:var(--line-soft);background:var(--gray-dark)">
    <div class="mb-8">
      <div class="divider reveal"></div>
      <h1 class="text-3xl md:text-4xl font-black mb-3">
        {{ $order->payment_status === 'paid' ? __('storefront.checkout_success_title') : ($order->payment_status === 'canceled' ? __('storefront.checkout_canceled_title') : __('storefront.checkout_failed_title')) }}
      </h1>
      <p style="color:var(--gray-light)">
        {{ $order->payment_status === 'paid' ? __('storefront.checkout_success_copy') : ($order->payment_status === 'canceled' ? __('storefront.checkout_canceled_copy') : __('storefront.checkout_failed_copy')) }}
      </p>
    </div>

    <div class="grid gap-5 md:grid-cols-2 mb-8">
      <div class="border p-5" style="border-color:var(--line-soft);background:rgb(var(--white-rgb) / .03)">
        <p class="text-xs font-bold mb-2 checkout-eyebrow">{{ __('storefront.account.order_number') }}</p>
        <p class="text-xl font-black">{{ $order->order_number }}</p>
      </div>
      <div class="border p-5" style="border-color:var(--line-soft);background:rgb(var(--white-rgb) / .03)">
        <p class="text-xs font-bold mb-2 checkout-eyebrow">{{ __('storefront.account.payment_status') }}</p>
        <p class="text-xl font-black">{{ $order->payment_status }}</p>
      </div>
    </div>

    <div class="border p-6 mb-8" style="border-color:var(--line-soft);background:rgb(var(--white-rgb) / .03)">
      <div class="space-y-3 text-sm">
        <div class="flex items-start justify-between gap-4"><span style="color:var(--gray-light)">{{ __('storefront.account.subtotal') }}</span><x-frontend.price :amount="$order->subtotal" :currency="$order->currency" wrapper-class="items-end text-left" amount-class="font-bold" secondary-class="text-xs" note-class="text-[10px]" /></div>
        <div class="flex items-start justify-between gap-4"><span style="color:var(--gray-light)">{{ __('storefront.account.shipping_total') }}</span><x-frontend.price :amount="$order->shipping_total" :currency="$order->currency" wrapper-class="items-end text-left" amount-class="font-bold" secondary-class="text-xs" note-class="text-[10px]" /></div>
        <div class="flex items-start justify-between gap-4"><span style="color:var(--gray-light)">{{ __('storefront.account.tax_total') }}</span><x-frontend.price :amount="$order->tax_total" :currency="$order->currency" wrapper-class="items-end text-left" amount-class="font-bold" secondary-class="text-xs" note-class="text-[10px]" /></div>
        <div class="flex items-start justify-between gap-4 border-t pt-4 text-lg" style="border-color:var(--line-soft)"><span>{{ __('storefront.account.grand_total') }}</span><x-frontend.price :amount="$order->grand_total" :currency="$order->currency" wrapper-class="items-end text-left" amount-class="font-bold" secondary-class="text-xs" note-class="text-[10px]" /></div>
      </div>
    </div>

    <div class="flex flex-wrap gap-4">
      @if ($order->payment_status === 'paid')
        <a href="{{ route('storefront.orders.index') }}" class="btn-primary"><span>{{ __('storefront.checkout_view_orders') }}</span></a>
      @else
        <a href="{{ route('storefront.checkout.show') }}" class="btn-primary"><span>{{ __('storefront.checkout_try_again') }}</span></a>
      @endif
      <a href="{{ route('storefront.home') }}" class="btn-outline"><span>{{ __('storefront.common.home') }}</span></a>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
refreshNavbarCartSummary();
</script>
@endpush

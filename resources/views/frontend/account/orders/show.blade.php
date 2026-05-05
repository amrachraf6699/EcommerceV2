@php($title = $order->order_number)

@extends('frontend.layouts.app')

@section('content')
<section class="px-6 pb-20" style="padding-top:120px">
  <div class="max-w-7xl mx-auto grid gap-8 lg:grid-cols-[280px_minmax(0,1fr)]">
    @include('frontend.account.partials.sidebar')

    <div class="space-y-8">
      <div class="border p-8" style="border-color:var(--line-soft);background:var(--gray-dark)">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
          <div>
            <div class="divider reveal"></div>
            <h1 class="text-3xl font-black mb-3">{{ $order->order_number }}</h1>
            <p style="color:var(--gray-light)">{{ __('storefront.account.order_details_copy') }}</p>
          </div>
          <a href="{{ route('storefront.orders.index') }}" class="btn-outline"><span>{{ __('storefront.account.back_to_orders') }}</span></a>
        </div>

        <div class="grid gap-5 md:grid-cols-3">
          <div class="border p-5" style="border-color:var(--line-soft);background:rgb(var(--white-rgb) / .03)">
            <p class="text-xs font-bold mb-2" style="letter-spacing:0.14em;color:var(--gray-light)">{{ __('storefront.account.order_status') }}</p>
            <p class="text-lg font-black">{{ $order->status }}</p>
          </div>
          <div class="border p-5" style="border-color:var(--line-soft);background:rgb(var(--white-rgb) / .03)">
            <p class="text-xs font-bold mb-2" style="letter-spacing:0.14em;color:var(--gray-light)">{{ __('storefront.account.payment_status') }}</p>
            <p class="text-lg font-black">{{ $order->payment_status }}</p>
          </div>
          <div class="border p-5" style="border-color:var(--line-soft);background:rgb(var(--white-rgb) / .03)">
            <p class="text-xs font-bold mb-2" style="letter-spacing:0.14em;color:var(--gray-light)">{{ __('storefront.account.fulfillment_status') }}</p>
            <p class="text-lg font-black">{{ $order->fulfillment_status }}</p>
          </div>
        </div>
      </div>

      <div class="border p-8" style="border-color:var(--line-soft);background:var(--gray-dark)">
        <h2 class="text-2xl font-black mb-6">{{ __('storefront.account.items') }}</h2>
        <div class="space-y-4">
          @foreach ($order->items as $item)
            <div class="border p-5" style="border-color:var(--line-soft);background:rgb(var(--white-rgb) / .03)">
              <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                  <p class="text-lg font-black">{{ $item->product_name }}</p>
                  @if ($item->variant_name)
                    <p class="text-sm" style="color:var(--gray-light)">{{ $item->variant_name }}</p>
                  @endif
                  @if ($item->sku)
                    <p class="text-xs mt-2" style="letter-spacing:0.12em;color:var(--gray-light)">{{ $item->sku }}</p>
                  @endif
                  @if (($item->product_id && ! $item->product) || ($item->product_variant_id && ! $item->variant))
                    <p class="text-xs mt-2" style="color:#f4ce7a">Deleted catalog record. Order snapshot preserved.</p>
                  @endif
                </div>
                <div class="text-left md:text-right">
                  <p class="text-sm" style="color:var(--gray-light)">{{ __('storefront.account.quantity_label', ['count' => $item->quantity]) }}</p>
                  <x-frontend.price :amount="$item->line_total" :currency="$order->currency" wrapper-class="items-end text-left" amount-class="text-lg font-black" secondary-class="text-xs" note-class="text-[10px]" />
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <div class="grid gap-8 lg:grid-cols-2">
        <div class="border p-8" style="border-color:var(--line-soft);background:var(--gray-dark)">
          <h2 class="text-2xl font-black mb-6">{{ __('storefront.account.shipping_address') }}</h2>
          <div class="space-y-2" style="color:var(--gray-light)">
            <p>{{ $order->shipping_address_line_1 ?: __('storefront.account.not_available') }}</p>
            @if ($order->shipping_address_line_2)<p>{{ $order->shipping_address_line_2 }}</p>@endif
            <p>{{ collect([$order->shipping_city, $order->shipping_state, $order->shipping_country])->filter()->implode(', ') ?: __('storefront.account.not_available') }}</p>
            @if ($order->shipping_postal_code)<p>{{ $order->shipping_postal_code }}</p>@endif
          </div>
        </div>

        <div class="border p-8" style="border-color:var(--line-soft);background:var(--gray-dark)">
          <h2 class="text-2xl font-black mb-6">{{ __('storefront.account.order_summary') }}</h2>
          <div class="space-y-3 text-sm">
            <div class="flex items-start justify-between gap-4"><span style="color:var(--gray-light)">{{ __('storefront.account.subtotal') }}</span><x-frontend.price :amount="$order->subtotal" :currency="$order->currency" wrapper-class="items-end text-left" amount-class="font-bold" secondary-class="text-xs" note-class="text-[10px]" /></div>
            <div class="flex items-start justify-between gap-4"><span style="color:var(--gray-light)">{{ __('storefront.checkout_coupon_code') }}</span><span class="font-bold">{{ $order->coupon_code ?: __('storefront.account.not_available') }}</span></div>
            <div class="flex items-start justify-between gap-4"><span style="color:var(--gray-light)">{{ __('storefront.account.discount_total') }}</span><x-frontend.price :amount="$order->discount_total" :currency="$order->currency" wrapper-class="items-end text-left" amount-class="font-bold" secondary-class="text-xs" note-class="text-[10px]" /></div>
            <div class="flex items-start justify-between gap-4"><span style="color:var(--gray-light)">{{ __('storefront.account.shipping_total') }}</span><x-frontend.price :amount="$order->shipping_total" :currency="$order->currency" wrapper-class="items-end text-left" amount-class="font-bold" secondary-class="text-xs" note-class="text-[10px]" /></div>
            <div class="flex items-start justify-between gap-4"><span style="color:var(--gray-light)">{{ __('storefront.account.tax_total') }}</span><x-frontend.price :amount="$order->tax_total" :currency="$order->currency" wrapper-class="items-end text-left" amount-class="font-bold" secondary-class="text-xs" note-class="text-[10px]" /></div>
            <div class="pt-3 mt-3 flex items-start justify-between gap-4 border-t text-lg" style="border-color:var(--line-soft)"><span>{{ __('storefront.account.grand_total') }}</span><x-frontend.price :amount="$order->grand_total" :currency="$order->currency" wrapper-class="items-end text-left" amount-class="font-bold" secondary-class="text-xs" note-class="text-[10px]" /></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

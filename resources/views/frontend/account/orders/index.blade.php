@php($title = __('storefront.account.orders'))

@extends('frontend.layouts.app')

@section('content')
<section class="px-6 pb-20" style="padding-top:120px">
  <div class="max-w-7xl mx-auto grid gap-8 lg:grid-cols-[280px_minmax(0,1fr)]">
    @include('frontend.account.partials.sidebar')

    <div class="border p-8" style="border-color:var(--line-soft);background:var(--gray-dark)">
      <div class="mb-8">
        <div class="divider reveal"></div>
        <h1 class="text-3xl font-black mb-3">{{ __('storefront.account.orders') }}</h1>
        <p style="color:var(--gray-light)">{{ __('storefront.account.orders_copy') }}</p>
      </div>

      <div class="space-y-5">
        @forelse ($orders as $order)
          <a href="{{ route('storefront.orders.show', $order) }}" class="block border p-6 transition hover:-translate-y-1" style="border-color:var(--line-soft);background:rgb(var(--white-rgb) / .03)">
            <div class="grid gap-4 md:grid-cols-[1fr_auto_auto] md:items-center">
              <div>
                <p class="text-xs font-bold mb-2" style="letter-spacing:0.14em;color:var(--gray-light)">{{ __('storefront.account.order_number') }}</p>
                <p class="text-2xl font-black">{{ $order->order_number }}</p>
              </div>
              <div>
                <p class="text-xs font-bold mb-2" style="letter-spacing:0.14em;color:var(--gray-light)">{{ __('storefront.account.order_status') }}</p>
                <p class="text-sm font-bold">{{ $order->status_label }}</p>
              </div>
              <div class="text-left md:text-right">
                <x-frontend.price :amount="$order->grand_total" :currency="$order->currency" wrapper-class="items-end text-left" amount-class="text-lg font-black" secondary-class="text-xs" note-class="text-[10px]" />
                <p class="text-sm" style="color:var(--gray-light)">{{ optional($order->placed_at)->format('Y-m-d H:i') ?: $order->created_at->format('Y-m-d H:i') }}</p>
              </div>
            </div>
          </a>
        @empty
          <div class="border px-5 py-10 text-center" style="border-color:var(--line-soft);background:rgb(var(--white-rgb) / .03)">
            <p class="text-xl font-black mb-2">{{ __('storefront.account.no_orders') }}</p>
            <p style="color:var(--gray-light)">{{ __('storefront.account.no_orders_copy') }}</p>
          </div>
        @endforelse
      </div>

      @if ($orders->hasPages())
        <div class="mt-8">
          {{ $orders->links() }}
        </div>
      @endif
    </div>
  </div>
</section>
@endsection

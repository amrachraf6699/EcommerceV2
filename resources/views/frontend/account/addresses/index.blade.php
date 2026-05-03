@php($title = __('storefront.account.addresses'))

@extends('frontend.layouts.app')

@section('content')
<section class="px-6 pb-20" style="padding-top:120px">
  <div class="max-w-7xl mx-auto grid gap-8 lg:grid-cols-[280px_minmax(0,1fr)]">
    @include('frontend.account.partials.sidebar')

    <div class="space-y-8">
      @if (session('success'))
        <div class="border px-5 py-4 text-sm" style="border-color:var(--line-mid);background:rgb(var(--white-rgb) / .04)">
          {{ session('success') }}
        </div>
      @endif

      <div class="border p-8" style="border-color:var(--line-soft);background:var(--gray-dark)">
        <div class="mb-8">
          <div class="divider reveal"></div>
          <h1 class="text-3xl font-black mb-3">{{ __('storefront.account.addresses') }}</h1>
          <p style="color:var(--gray-light)">{{ __('storefront.account.addresses_copy') }}</p>
        </div>

        <form method="POST" action="{{ route('storefront.addresses.store') }}" class="grid gap-5 md:grid-cols-2">
          @csrf
          @include('frontend.account.addresses.partials.form', ['address' => null, 'submitLabel' => __('storefront.account.add_address')])
        </form>
      </div>

      <div class="space-y-6">
        @forelse ($addresses as $address)
          <div class="border p-8" style="border-color:var(--line-soft);background:var(--gray-dark)">
            <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
              <div>
                <h2 class="text-2xl font-black">{{ $address->label ?: __('storefront.account.saved_address') }}</h2>
                <p class="mt-2 text-sm" style="color:var(--gray-light)">
                  {{ collect([$address->city, $address->state, $address->country])->filter()->implode(', ') }}
                </p>
              </div>
              <div class="flex flex-wrap gap-2">
                @if ($address->is_default_shipping)
                  <span class="border px-3 py-2 text-xs font-bold" style="border-color:var(--line-mid)">{{ __('storefront.account.default_shipping') }}</span>
                @endif
                @if ($address->is_default_billing)
                  <span class="border px-3 py-2 text-xs font-bold" style="border-color:var(--line-mid)">{{ __('storefront.account.default_billing') }}</span>
                @endif
              </div>
            </div>

            <form method="POST" action="{{ route('storefront.addresses.update', $address) }}" class="grid gap-5 md:grid-cols-2">
              @csrf
              @method('PUT')
              @include('frontend.account.addresses.partials.form', ['address' => $address, 'submitLabel' => __('storefront.account.update_address')])
            </form>

            <form method="POST" action="{{ route('storefront.addresses.destroy', $address) }}" class="mt-4">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn-outline" onclick="return confirm(@json(__('storefront.account.delete_address_confirm')))"><span>{{ __('storefront.account.delete_address') }}</span></button>
            </form>
          </div>
        @empty
          <div class="border px-5 py-10 text-center" style="border-color:var(--line-soft);background:rgb(var(--white-rgb) / .03)">
            <p class="text-xl font-black mb-2">{{ __('storefront.account.no_addresses') }}</p>
            <p style="color:var(--gray-light)">{{ __('storefront.account.no_addresses_copy') }}</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</section>
@endsection

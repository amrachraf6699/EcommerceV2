@php($title = __('storefront.account.profile'))

@extends('frontend.layouts.app')

@section('content')
<section class="px-6 pb-20" style="padding-top:120px">
  <div class="max-w-7xl mx-auto grid gap-8 lg:grid-cols-[280px_minmax(0,1fr)]">
    @include('frontend.account.partials.sidebar')

    <div class="space-y-8">
      <div class="border p-8" style="border-color:var(--line-soft);background:var(--gray-dark)">
        <div class="mb-8">
          <div class="divider reveal"></div>
          <h1 class="text-3xl font-black mb-3">{{ __('storefront.account.profile') }}</h1>
          <p style="color:var(--gray-light)">{{ __('storefront.account.profile_copy') }}</p>
        </div>

        <form method="POST" action="{{ route('storefront.profile.update') }}" class="grid gap-5 md:grid-cols-2">
          @csrf
          @method('PUT')

          <div class="md:col-span-2">
            <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.account.name') }}</label>
            <input type="text" name="name" class="input-field" value="{{ old('name', $customer->name) }}" required>
            @error('name')<p class="mt-2 text-sm" style="color:#ffb2b2">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.auth.email') }}</label>
            <input type="email" name="email" class="input-field" value="{{ old('email', $customer->email) }}" required>
            @error('email')<p class="mt-2 text-sm" style="color:#ffb2b2">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.account.phone') }}</label>
            <input type="text" name="phone" class="input-field" value="{{ old('phone', $customer->phone) }}">
            @error('phone')<p class="mt-2 text-sm" style="color:#ffb2b2">{{ $message }}</p>@enderror
          </div>

          <div class="md:col-span-2">
            <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.account.country') }}</label>
            <select name="country" class="sort-select country-select">
              @include('frontend.partials.country-options', ['selectedCountry' => old('country', $customer->country)])
            </select>
            @error('country')<p class="mt-2 text-sm" style="color:#ffb2b2">{{ $message }}</p>@enderror
          </div>

          <div class="md:col-span-2">
            <button type="submit" class="btn-primary"><span>{{ __('storefront.account.save_profile') }}</span></button>
          </div>
        </form>
      </div>

      <div class="border p-8" style="border-color:var(--line-soft);background:var(--gray-dark)">
        <div class="mb-8">
          <h2 class="text-2xl font-black mb-3">{{ __('storefront.account.password_title') }}</h2>
          <p style="color:var(--gray-light)">{{ __('storefront.account.password_copy') }}</p>
        </div>

        <form method="POST" action="{{ route('storefront.profile.password.update') }}" class="grid gap-5 md:grid-cols-2">
          @csrf
          @method('PUT')

          <div class="md:col-span-2">
            <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.account.current_password') }}</label>
            <input type="password" name="current_password" class="input-field" required>
            @error('current_password')<p class="mt-2 text-sm" style="color:#ffb2b2">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.auth.password') }}</label>
            <input type="password" name="password" class="input-field" required>
            @error('password')<p class="mt-2 text-sm" style="color:#ffb2b2">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.account.password_confirmation') }}</label>
            <input type="password" name="password_confirmation" class="input-field" required>
          </div>

          <div class="md:col-span-2">
            <button type="submit" class="btn-outline"><span>{{ __('storefront.account.save_password') }}</span></button>
          </div>
        </form>
      </div>

      <div class="border p-8" style="border-color:var(--line-soft);background:var(--gray-dark)">
        <div class="flex items-end justify-between gap-4 mb-6">
          <div>
            <h2 class="text-2xl font-black mb-2">{{ __('storefront.account.recent_orders') }}</h2>
            <p style="color:var(--gray-light)">{{ __('storefront.account.recent_orders_copy') }}</p>
          </div>
          <a href="{{ route('storefront.orders.index') }}" class="btn-outline hidden md:inline-flex"><span>{{ __('storefront.common.view_all') }}</span></a>
        </div>

        <div class="space-y-4">
          @forelse ($recentOrders as $order)
            <a href="{{ route('storefront.orders.show', $order) }}" class="block border p-5 transition hover:-translate-y-1" style="border-color:var(--line-soft);background:rgb(var(--white-rgb) / .03)">
              <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                  <p class="text-sm font-bold" style="letter-spacing:0.08em;color:var(--gray-light)">{{ $order->order_number }}</p>
                  <x-frontend.price :amount="$order->grand_total" :currency="$order->currency" amount-class="text-lg font-black" secondary-class="text-xs" note-class="text-[10px]" />
                </div>
                <div class="text-sm" style="color:var(--gray-light)">
                  {{ optional($order->placed_at)->format('Y-m-d H:i') ?: $order->created_at->format('Y-m-d H:i') }}
                </div>
              </div>
            </a>
          @empty
            <div class="border px-5 py-8 text-center" style="border-color:var(--line-soft);background:rgb(var(--white-rgb) / .03)">
              <p class="font-bold">{{ __('storefront.account.no_orders') }}</p>
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

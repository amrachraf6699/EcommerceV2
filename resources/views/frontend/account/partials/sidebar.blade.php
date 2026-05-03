@php
    $customer = auth('customer')->user();
@endphp

<aside class="space-y-5">
  <div class="border p-6" style="border-color:var(--line-soft);background:var(--gray-dark)">
    <p class="text-xs font-bold mb-2" style="letter-spacing:0.14em;color:var(--gray-light)">{{ __('storefront.account.dashboard') }}</p>
    <h2 class="text-2xl font-black mb-2">{{ $customer->name }}</h2>
    <p class="text-sm" style="color:var(--gray-light)">{{ $customer->email }}</p>
  </div>

  <div class="border p-3" style="border-color:var(--line-soft);background:rgb(var(--white-rgb) / .03)">
    <a href="{{ route('storefront.profile.edit') }}" class="account-nav-link {{ request()->routeIs('storefront.profile.*') ? 'is-active' : '' }}">{{ __('storefront.account.profile') }}</a>
    <a href="{{ route('storefront.orders.index') }}" class="account-nav-link {{ request()->routeIs('storefront.orders.*') ? 'is-active' : '' }}">{{ __('storefront.account.orders') }}</a>
    <a href="{{ route('storefront.addresses.index') }}" class="account-nav-link {{ request()->routeIs('storefront.addresses.*') ? 'is-active' : '' }}">{{ __('storefront.account.addresses') }}</a>
    <form method="POST" action="{{ route('storefront.auth.logout') }}">
      @csrf
      <button type="submit" class="account-nav-link w-full text-right">{{ __('storefront.auth.logout') }}</button>
    </form>
  </div>
</aside>

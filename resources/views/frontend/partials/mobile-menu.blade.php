@php
    $nextLocale = app()->getLocale() === 'ar' ? 'en' : 'ar';
    $customer = auth('customer')->user();
@endphp
<div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileMenu()"></div>
<div class="mobile-menu" id="mobileMenu">
  <button onclick="toggleMobileMenu()" style="position:absolute;top:20px;inset-inline-start:20px;color:var(--white)">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
  </button>
  <div class="flex flex-col gap-6">
    <a href="{{ route('storefront.home') }}" class="text-2xl font-bold {{ request()->routeIs('storefront.home') ? '' : 'text-neutral-400' }}">{{ __('storefront.common.home') }}</a>
    <a href="{{ route('storefront.categories.index') }}" class="text-2xl font-bold {{ request()->routeIs('storefront.categories.index', 'storefront.categories.show') ? '' : 'text-neutral-400' }}">{{ __('storefront.common.categories') }}</a>
    <a href="{{ route('storefront.catalog') }}" class="text-2xl font-bold {{ request()->routeIs('storefront.catalog') ? '' : 'text-neutral-400' }}">{{ __('storefront.common.products') }}</a>
    <a href="{{ route('storefront.contact.show') }}" class="text-2xl font-bold {{ request()->routeIs('storefront.contact.show') ? '' : 'text-neutral-400' }}">{{ __('storefront.common.contact') }}</a>
    @if ($customer)
      <div class="mobile-account-dropdown">
        <button
          type="button"
          class="mobile-account-trigger"
          onclick="toggleMobileAccountMenu()"
          aria-expanded="false"
          aria-controls="mobileAccountMenu"
          id="mobileAccountTrigger"
        >
          <span>{{ __('storefront.account.dashboard') }}</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="mobile-account-trigger__icon">
            <polyline points="6 9 12 15 18 9"/>
          </svg>
        </button>
        <div class="mobile-account-menu" id="mobileAccountMenu">
          <a href="{{ route('storefront.profile.edit') }}" class="mobile-account-menu__link">{{ __('storefront.account.profile') }}</a>
          <a href="{{ route('storefront.orders.index') }}" class="mobile-account-menu__link">{{ __('storefront.account.orders') }}</a>
          <a href="{{ route('storefront.addresses.index') }}" class="mobile-account-menu__link">{{ __('storefront.account.addresses') }}</a>
          <form method="POST" action="{{ route('storefront.auth.logout') }}">
            @csrf
            <button type="submit" class="mobile-account-menu__link w-full text-right">{{ __('storefront.auth.logout') }}</button>
          </form>
        </div>
      </div>
    @else
      <div class="mobile-account-dropdown">
        <button
          type="button"
          class="mobile-account-trigger"
          onclick="toggleMobileAccountMenu()"
          aria-expanded="false"
          aria-controls="mobileAccountMenu"
          id="mobileAccountTrigger"
        >
          <span>{{ __('storefront.account.dashboard') }}</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="mobile-account-trigger__icon">
            <polyline points="6 9 12 15 18 9"/>
          </svg>
        </button>
        <div class="mobile-account-menu" id="mobileAccountMenu">
          <a href="{{ route('storefront.auth.login') }}" class="mobile-account-menu__link">{{ __('storefront.auth.login') }}</a>
          <a href="{{ route('storefront.auth.register') }}" class="mobile-account-menu__link">{{ __('storefront.auth.register') }}</a>
        </div>
      </div>
    @endif
    <a href="{{ storefront_switch_url($nextLocale) }}" class="text-lg font-bold text-neutral-300">{{ storefront_locale_name($nextLocale) }}</a>
  </div>
</div>

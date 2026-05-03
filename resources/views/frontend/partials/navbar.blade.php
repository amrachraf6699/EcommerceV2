@php
    $nextLocale = app()->getLocale() === 'ar' ? 'en' : 'ar';
    $customer = auth('customer')->user();
@endphp
<nav class="navbar px-4 md:px-6 py-4">
  <div class="max-w-7xl mx-auto hidden md:flex items-center justify-between gap-6">
    <a href="{{ route('storefront.home') }}" class="flex items-center gap-3 shrink-0">
      <div class="h-16 flex items-center justify-center overflow-hidden" style="background:transparent;color:inherit">
        @if ($frontendBrand['logo_url'])
          <img src="{{ $frontendBrand['logo_url'] }}" alt="{{ $frontendBrand['name'] }}" class="w-full h-full object-contain">
        @else
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M2 18L6 6L12 14L16 8L22 18H2Z" style="fill:var(--black)"/></svg>
        @endif
      </div>
    </a>

    <div class="flex items-center justify-center gap-8 flex-1">
      <a href="{{ route('storefront.home') }}" class="nav-link text-sm {{ request()->routeIs('storefront.home') ? 'is-active' : '' }}">{{ __('storefront.common.home') }}</a>
      <a href="{{ route('storefront.categories.index') }}" class="nav-link text-sm {{ request()->routeIs('storefront.categories.index', 'storefront.categories.show') ? 'is-active' : '' }}">{{ __('storefront.common.categories') }}</a>
      <a href="{{ route('storefront.catalog') }}" class="nav-link text-sm {{ request()->routeIs('storefront.catalog') ? 'is-active' : '' }}">{{ __('storefront.common.products') }}</a>
      <a href="{{ route('storefront.contact.show') }}" class="nav-link text-sm {{ request()->routeIs('storefront.contact.show') ? 'is-active' : '' }}">{{ __('storefront.common.contact') }}</a>
    </div>

    <div class="flex items-center gap-3 shrink-0">
      <div class="navbar-cart-dropdown">
        <button
          class="relative navbar-cart-trigger"
          type="button"
          style="color:var(--gray-light)"
          aria-label="{{ __('storefront.common.add_to_cart') }}"
          aria-expanded="false"
          aria-controls="navbarCartMenu"
          id="navbarCartTrigger"
          onclick="toggleNavbarCartDropdown()"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          <span class="count-badge" id="cartCount">{{ $frontendCartSummary['items_count'] }}</span>
        </button>
        <div class="navbar-cart-menu" id="navbarCartMenu">
          <div class="navbar-cart-menu__content">
            <p class="navbar-cart-menu__eyebrow">{{ __('storefront.cart_summary') }}</p>
            <div class="navbar-cart-menu__row">
              <span>{{ __('storefront.cart_items_label') }}</span>
              <strong id="cartSummaryCount">{{ $frontendCartSummary['items_count'] }}</strong>
            </div>
            <div class="navbar-cart-menu__row">
              <span>{{ __('storefront.account.subtotal') }}</span>
              <div id="cartSummarySubtotal" data-price-root data-bhd-amount="{{ number_format((float) $frontendCartSummary['subtotal'], 2, '.', '') }}" data-bhd-currency="{{ $frontendCartSummary['currency'] }}" class="text-left">
                <strong data-bhd-primary>{{ storefront_format_money($frontendCartSummary['subtotal'], $frontendCartSummary['currency']) }}</strong>
              </div>
            </div>
            <p class="navbar-cart-menu__empty {{ $frontendCartSummary['items_count'] > 0 ? 'hidden' : '' }}" id="cartSummaryEmpty">{{ __('storefront.cart_empty') }}</p>
            <a href="{{ route('storefront.cart.show') }}" class="btn-outline w-full navbar-cart-menu__cta">
              <span>{{ __('storefront.cart_view') }}</span>
            </a>
            <a href="{{ route('storefront.checkout.show') }}" class="btn-primary w-full navbar-cart-menu__cta" id="cartCheckoutButton">
              <span>{{ __('storefront.checkout') }}</span>
            </a>
          </div>
        </div>
      </div>

      <button
        type="button"
        class="navbar-actions-trigger"
        aria-label="{{ __('storefront.common.search') }}"
        title="{{ __('storefront.common.search') }}"
        onclick="openSearchModal()"
      >
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      </button>

      <a
        href="{{ storefront_switch_url($nextLocale) }}"
        class="navbar-actions-trigger"
        aria-label="{{ __('storefront.common.language') }}"
        title="{{ storefront_locale_name($nextLocale) }}"
      >
        <i class="bx bx-globe text-lg"></i>
      </a>

      @if ($customer)
        <a href="{{ route('storefront.profile.edit') }}" class="btn-outline text-sm py-2 px-5 inline-flex"><span>{{ __('storefront.account.dashboard') }}</span></a>
      @else
        <button onclick="openLoginModal()" class="btn-primary text-sm py-2 px-5"><span>{{ __('storefront.auth.login') }}</span></button>
      @endif
    </div>
  </div>

  <div class="max-w-7xl mx-auto md:hidden relative flex items-center justify-between min-h-[64px]">
    <div class="flex items-center gap-2 shrink-0">
      <div class="navbar-actions-dropdown">
        <button
          type="button"
          class="navbar-actions-trigger"
          onclick="toggleNavbarActionsDropdown()"
          aria-expanded="false"
          aria-controls="navbarActionsMenu"
          id="navbarActionsTrigger"
          aria-label="Actions"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
            <polyline points="6 9 12 15 18 9"/>
          </svg>
        </button>
        <div class="navbar-actions-menu" id="navbarActionsMenu">
          <button type="button" class="navbar-actions-menu__link" onclick="openSearchModal()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <span>{{ __('storefront.common.search') }}</span>
          </button>
          <a
            href="{{ storefront_switch_url($nextLocale) }}"
            class="navbar-actions-menu__link"
            aria-label="{{ __('storefront.common.language') }}"
            title="{{ storefront_locale_name($nextLocale) }}"
          >
            <i class="bx bx-globe text-lg"></i>
            <span>{{ storefront_locale_name($nextLocale) }}</span>
          </a>
        </div>
      </div>

      <button class="navbar-actions-trigger" type="button" onclick="toggleMobileMenu()" aria-label="Menu">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
    </div>

    <a href="{{ route('storefront.home') }}" class="absolute left-1/2 -translate-x-1/2 flex items-center justify-center">
      <div class="h-14 flex items-center justify-center overflow-hidden" style="background:transparent;color:inherit">
        @if ($frontendBrand['logo_url'])
          <img src="{{ $frontendBrand['logo_url'] }}" alt="{{ $frontendBrand['name'] }}" class="w-full h-full object-contain">
        @else
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M2 18L6 6L12 14L16 8L22 18H2Z" style="fill:var(--black)"/></svg>
        @endif
      </div>
    </a>

    <div class="flex items-center gap-2 shrink-0 ms-auto">
      <a
        href="{{ route('storefront.cart.show') }}"
        class="relative navbar-cart-trigger"
        style="color:var(--gray-light)"
        aria-label="{{ __('storefront.common.add_to_cart') }}"
      >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          <span class="count-badge">{{ $frontendCartSummary['items_count'] }}</span>
      </a>

      @if ($customer)
        <a href="{{ route('storefront.profile.edit') }}" class="btn-outline text-xs py-2 px-3 inline-flex"><span>{{ __('storefront.account.dashboard') }}</span></a>
      @else
        <button onclick="openLoginModal()" class="btn-primary text-xs py-2 px-3"><span>{{ __('storefront.auth.login') }}</span></button>
      @endif
    </div>
  </div>
</nav>

<div class="modal-overlay" id="searchModal">
  <div class="modal-box" style="max-width:540px">
    <div style="padding:32px">
      <div class="flex items-center justify-between gap-4 mb-6">
        <div>
          <p class="text-xs font-black mb-2" style="letter-spacing:.18em;color:var(--gray-light)">{{ __('storefront.common.search') }}</p>
          <h2 class="text-2xl font-black">{{ __('storefront.catalog.title') }}</h2>
        </div>
        <button type="button" onclick="closeSearchModal()" style="color:var(--gray-light)">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>

      <form method="GET" action="{{ route('storefront.catalog') }}" class="flex flex-col gap-4">
        <div class="relative">
          <input
            type="text"
            name="search"
            id="searchModalInput"
            class="input-field"
            placeholder="{{ __('storefront.catalog.search_placeholder') }}"
            style="padding-inline-start:48px"
          >
          <span style="position:absolute;inset-inline-start:16px;top:50%;transform:translateY(-50%);color:var(--gray-light);display:flex">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          </span>
        </div>
        <button type="submit" class="btn-primary w-full"><span>{{ __('storefront.common.search') }}</span></button>
      </form>
    </div>
  </div>
</div>

@php($title = __('storefront.checkout'))
@php($summaryCurrency = $cart?->currency ?? 'BHD')

@extends('frontend.layouts.app')

@section('content')
<section class="px-6 pb-20" style="padding-top:120px">
  <div class="max-w-7xl mx-auto space-y-8">
    <div class="max-w-3xl">
      <div class="divider reveal"></div>
      <h1 class="text-4xl font-black mb-3">{{ __('storefront.checkout_title') }}</h1>
      <p style="color:var(--gray-light)">{{ __('storefront.checkout_copy') }}</p>
    </div>

    @if ($errors->has('cart'))
      <div class="checkout-notice checkout-notice--error">
        {{ $errors->first('cart') }}
      </div>
    @endif

    @if (! $tapCheckoutAvailable)
      <div class="checkout-notice checkout-notice--maintenance">
        {{ __('storefront.checkout_maintenance') }}
      </div>
    @endif

    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px]">
      <div class="border p-8" style="border-color:var(--line-soft);background:var(--gray-dark)">
        <form id="checkoutForm" method="POST" action="{{ route('storefront.checkout.store') }}" class="grid gap-5 md:grid-cols-2">
          @csrf

          <div>
            <label class="text-xs font-bold mb-2 block checkout-label">{{ __('storefront.checkout_first_name') }}</label>
            <input type="text" name="first_name" class="input-field" value="{{ $checkoutForm['first_name'] }}" required>
            @error('first_name')<p class="mt-2 text-sm checkout-error">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="text-xs font-bold mb-2 block checkout-label">{{ __('storefront.checkout_last_name') }}</label>
            <input type="text" name="last_name" class="input-field" value="{{ $checkoutForm['last_name'] }}" required>
            @error('last_name')<p class="mt-2 text-sm checkout-error">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="text-xs font-bold mb-2 block checkout-label">{{ __('storefront.auth.email') }}</label>
            <input type="email" name="email" class="input-field" value="{{ $checkoutForm['email'] }}" @if(auth('customer')->check()) readonly @endif required>
            @error('email')<p class="mt-2 text-sm checkout-error">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="text-xs font-bold mb-2 block checkout-label">{{ __('storefront.account.phone') }}</label>
            <input type="text" name="phone" class="input-field" value="{{ $checkoutForm['phone'] }}">
            @error('phone')<p class="mt-2 text-sm checkout-error">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="text-xs font-bold mb-2 block checkout-label">{{ __('storefront.account.country') }}</label>
            <select name="country" class="sort-select country-select" required>
              @include('frontend.partials.country-options', ['selectedCountry' => $checkoutForm['country']])
            </select>
            @error('country')<p class="mt-2 text-sm checkout-error">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="text-xs font-bold mb-2 block checkout-label">{{ __('storefront.account.city') }}</label>
            <input type="text" name="city" class="input-field" value="{{ $checkoutForm['city'] }}" required>
            @error('city')<p class="mt-2 text-sm checkout-error">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="text-xs font-bold mb-2 block checkout-label">{{ __('storefront.account.state') }}</label>
            <input type="text" name="state" class="input-field" value="{{ $checkoutForm['state'] }}">
            @error('state')<p class="mt-2 text-sm checkout-error">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="text-xs font-bold mb-2 block checkout-label">{{ __('storefront.account.postal_code') }}</label>
            <input type="text" name="postal_code" class="input-field" value="{{ $checkoutForm['postal_code'] }}">
            @error('postal_code')<p class="mt-2 text-sm checkout-error">{{ $message }}</p>@enderror
          </div>

          <div class="md:col-span-2">
            <label class="text-xs font-bold mb-2 block checkout-label">{{ __('storefront.account.address_line_1') }}</label>
            <input type="text" name="address_line_1" class="input-field" value="{{ $checkoutForm['address_line_1'] }}" required>
            @error('address_line_1')<p class="mt-2 text-sm checkout-error">{{ $message }}</p>@enderror
          </div>

          <div class="md:col-span-2">
            <label class="text-xs font-bold mb-2 block checkout-label">{{ __('storefront.account.address_line_2') }}</label>
            <input type="text" name="address_line_2" class="input-field" value="{{ $checkoutForm['address_line_2'] }}">
            @error('address_line_2')<p class="mt-2 text-sm checkout-error">{{ $message }}</p>@enderror
          </div>

          <div class="md:col-span-2">
            <label class="text-xs font-bold mb-2 block checkout-label">{{ __('storefront.checkout_note') }}</label>
            <textarea name="customer_note" class="input-field checkout-textarea">{{ $checkoutForm['customer_note'] }}</textarea>
            @error('customer_note')<p class="mt-2 text-sm checkout-error">{{ $message }}</p>@enderror
          </div>

          <div class="md:col-span-2">
            <button type="submit" class="btn-primary w-full" @disabled(! $tapCheckoutAvailable || ! $cart || $cart->items->isEmpty() || $checkoutSummary['error'])>
              <span>{{ __('storefront.checkout_pay_with_tap') }}</span>
            </button>
          </div>
        </form>
      </div>

      <aside class="border p-8 h-fit" style="border-color:var(--line-soft);background:var(--gray-dark)">
        <div class="space-y-6">
          <div>
            <p class="checkout-eyebrow">{{ __('storefront.cart_summary') }}</p>
            <h2 class="text-2xl font-black mt-3">{{ __('storefront.account.order_summary') }}</h2>
          </div>

          @if (! $cart || $cart->items->isEmpty())
            <p style="color:var(--gray-light)">{{ __('storefront.checkout_empty_cart') }}</p>
          @else
            <div class="space-y-4">
              @foreach ($cart->items as $item)
                <div class="checkout-item">
                  <div>
                    <p class="font-black">{{ $item->product_name }}</p>
                    @if ($item->variant_name)
                      <p class="text-sm" style="color:var(--gray-light)">{{ $item->variant_name }}</p>
                    @endif
                  </div>
                  <div class="text-left">
                    <p class="text-sm" style="color:var(--gray-light)">{{ __('storefront.account.quantity_label', ['count' => $item->quantity]) }}</p>
                    <x-frontend.price :amount="$item->line_total" :currency="$cart->currency" amount-class="font-black" secondary-class="text-xs" note-class="text-[10px]" />
                  </div>
                </div>
              @endforeach
            </div>

            <div class="space-y-3 border-t pt-6" style="border-color:var(--line-soft)">
              <div>
                <label class="text-xs font-bold mb-2 block checkout-label">{{ __('storefront.checkout_coupon_code') }}</label>
                <div class="grid gap-3 grid-cols-[minmax(0,1fr)_auto]">
                  <input
                    type="text"
                    id="checkoutCouponCodeInput"
                    name="coupon_code"
                    form="checkoutForm"
                    class="input-field"
                    value="{{ $checkoutForm['coupon_code'] }}"
                    placeholder="{{ __('storefront.checkout_coupon_placeholder') }}"
                    dir="ltr"
                  >
                  <button
                    type="button"
                    class="btn-primary checkout-coupon-status"
                    id="checkoutApplyCouponButton"
                  >
                    <span id="checkoutApplyCouponButtonText">{{ __('storefront.checkout_coupon_apply') }}</span>
                    <span id="checkoutApplyCouponButtonLoading" class="checkout-coupon-status__loading" hidden>
                      <span class="checkout-coupon-status__spinner" aria-hidden="true"></span>
                    </span>
                  </button>
                </div>
                <p
                  id="checkoutCouponMessage"
                  class="mt-2 text-xs"
                  style="color:{{ $checkoutSummary['coupon_error'] ? '#ffd27d' : 'var(--gray-light)' }}"
                >{{ $checkoutSummary['coupon_error'] ?: ($checkoutSummary['coupon_applied'] ? __('storefront.checkout_coupon_applied') : __('storefront.checkout_coupon_hint')) }}</p>
                @error('coupon_code')<p class="mt-2 text-sm checkout-error">{{ $message }}</p>@enderror
              </div>
            </div>

            <div class="space-y-3 pt-2">
              <div class="flex items-center justify-between"><span style="color:var(--gray-light)">{{ __('storefront.cart_items_label') }}</span><strong>{{ $cart->item_count }}</strong></div>
              <div class="flex items-start justify-between gap-4">
                <span style="color:var(--gray-light)">{{ __('storefront.account.subtotal') }}</span>
                <div
                  id="checkoutSubtotalPrice"
                  class="price-display items-end text-left"
                  data-price-root
                  data-bhd-amount="{{ number_format((float) $checkoutSummary['subtotal'], 2, '.', '') }}"
                  data-bhd-currency="{{ $summaryCurrency }}"
                >
                  <div><span class="font-bold" data-bhd-primary>{{ storefront_format_money($checkoutSummary['subtotal'], $summaryCurrency) }}</span></div>
                </div>
              </div>
              <div
                id="checkoutDiscountRow"
                class="flex items-start justify-between gap-4 {{ (float) $checkoutSummary['discount_total'] > 0 ? '' : 'hidden' }}"
              >
                <span style="color:var(--gray-light)">{{ __('storefront.account.discount_total') }}</span>
                <div
                  id="checkoutDiscountPrice"
                  class="price-display items-end text-left"
                  data-price-root
                  data-bhd-amount="{{ number_format((float) $checkoutSummary['discount_total'], 2, '.', '') }}"
                  data-bhd-currency="{{ $summaryCurrency }}"
                >
                  <div><span class="font-bold" data-bhd-primary>{{ storefront_format_money($checkoutSummary['discount_total'], $summaryCurrency) }}</span></div>
                </div>
              </div>
              <div class="flex items-start justify-between gap-4">
                <span style="color:var(--gray-light)">{{ __('storefront.account.shipping_total') }}</span>
                <div
                  id="checkoutShippingPrice"
                  class="price-display items-end text-left"
                  data-price-root
                  data-bhd-amount="{{ number_format((float) $checkoutSummary['shipping_total'], 2, '.', '') }}"
                  data-bhd-currency="{{ $summaryCurrency }}"
                >
                  <div><span class="font-bold" data-bhd-primary>{{ storefront_format_money($checkoutSummary['shipping_total'], $summaryCurrency) }}</span></div>
                </div>
              </div>
              <div class="flex items-start justify-between gap-4">
                <span style="color:var(--gray-light)">{{ __('storefront.account.tax_total') }}</span>
                <div
                  id="checkoutTaxPrice"
                  class="price-display items-end text-left"
                  data-price-root
                  data-bhd-amount="{{ number_format((float) $checkoutSummary['tax_total'], 2, '.', '') }}"
                  data-bhd-currency="{{ $summaryCurrency }}"
                >
                  <div><span class="font-bold" data-bhd-primary>{{ storefront_format_money($checkoutSummary['tax_total'], $summaryCurrency) }}</span></div>
                </div>
              </div>
              <div class="flex items-start justify-between gap-4 border-t pt-4 text-lg" style="border-color:var(--line-soft)">
                <span>{{ __('storefront.account.grand_total') }}</span>
                <div
                  id="checkoutGrandTotalPrice"
                  class="price-display items-end text-left"
                  data-price-root
                  data-bhd-amount="{{ number_format((float) $checkoutSummary['grand_total'], 2, '.', '') }}"
                  data-bhd-currency="{{ $summaryCurrency }}"
                >
                  <div><span class="font-bold" data-bhd-primary>{{ storefront_format_money($checkoutSummary['grand_total'], $summaryCurrency) }}</span></div>
                </div>
              </div>
              <div class="space-y-2 border-t pt-4" style="border-color:var(--line-soft)">
                <p
                  id="checkoutSummaryMessage"
                  class="{{ $checkoutSummary['error'] ? '' : 'hidden' }} text-sm"
                  style="color:#ffb2b2"
                >{{ $checkoutSummary['error'] }}</p>
              </div>
            </div>
          @endif
        </div>
      </aside>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const countrySelect = document.querySelector('select[name="country"]');
  const emailInput = document.querySelector('input[name="email"]');
  const couponInput = document.getElementById('checkoutCouponCodeInput');
  const applyCouponButton = document.getElementById('checkoutApplyCouponButton');
  const applyCouponButtonText = document.getElementById('checkoutApplyCouponButtonText');
  const applyCouponButtonLoading = document.getElementById('checkoutApplyCouponButtonLoading');
  const summaryMessage = document.getElementById('checkoutSummaryMessage');
  const couponMessage = document.getElementById('checkoutCouponMessage');
  const discountRow = document.getElementById('checkoutDiscountRow');
  const submitButton = document.querySelector('button[type="submit"]');
  const summaryEndpoint = @json(route('storefront.checkout.summary', ['locale' => app()->getLocale()]));
  const detectedCountryNameMap = @json($detectedCountryNameMap);
  const couponHintText = @json(__('storefront.checkout_coupon_hint'));
  let activeRequest = null;

  function setPrice(rootId, amount, currency) {
    const root = document.getElementById(rootId);
    const primary = root?.querySelector('[data-bhd-primary]');

    if (!root || !primary) {
      return;
    }

    root.dataset.bhdAmount = Number(amount || 0).toFixed(2);
    root.dataset.bhdCurrency = currency || 'BHD';
    root.dataset.basePriceLabel = `${Number(amount || 0).toFixed(2)} ${currency || 'BHD'}`;
    primary.textContent = root.dataset.basePriceLabel;

    if (typeof renderPriceNode === 'function') {
      renderPriceNode(root);
    }
  }

  function setDiscountVisibility(amount) {
    if (!discountRow) {
      return;
    }

    discountRow.classList.toggle('hidden', Number(amount || 0) <= 0);
  }

  function setCouponLoadingState(isLoading) {
    if (!applyCouponButton || !applyCouponButtonText || !applyCouponButtonLoading) {
      return;
    }

    applyCouponButton.disabled = isLoading;
    applyCouponButton.classList.toggle('is-loading', isLoading);
    applyCouponButtonText.hidden = isLoading;
    applyCouponButtonLoading.hidden = !isLoading;
    applyCouponButton.setAttribute('aria-busy', isLoading ? 'true' : 'false');
  }

  async function refreshCheckoutSummary() {
    if (!countrySelect) {
      return;
    }

    if (activeRequest) {
      activeRequest.abort();
    }

    activeRequest = new AbortController();
    setCouponLoadingState(Boolean(couponInput?.value.trim()));

    try {
      const query = new URLSearchParams({
        country: countrySelect.value || '',
        email: emailInput?.value || '',
        coupon_code: couponInput?.value || '',
      });

      const response = await fetch(`${summaryEndpoint}?${query.toString()}`, {
        headers: {
          'Accept': 'application/json',
        },
        signal: activeRequest.signal,
      });

      if (!response.ok) {
        throw new Error('Unable to load checkout summary.');
      }

      const payload = await response.json();
      const summary = payload.summary || {};
      const currency = @json($summaryCurrency);
      const error = summary.error || '';

      setPrice('checkoutSubtotalPrice', Number(summary.subtotal || 0), currency);
      setPrice('checkoutDiscountPrice', Number(summary.discount_total || 0), currency);
      setPrice('checkoutShippingPrice', Number(summary.shipping_total || 0), currency);
      setPrice('checkoutTaxPrice', Number(summary.tax_total || 0), currency);
      setPrice('checkoutGrandTotalPrice', Number(summary.grand_total || 0), currency);
      setDiscountVisibility(Number(summary.discount_total || 0));

      if (summaryMessage) {
        summaryMessage.textContent = error;
        summaryMessage.classList.toggle('hidden', !error);
      }

      if (couponMessage) {
        const couponError = summary.coupon_error || '';
        const couponApplied = Boolean(summary.coupon_applied);

        couponMessage.textContent = couponError || (couponApplied ? @json(__('storefront.checkout_coupon_applied')) : couponHintText);
        couponMessage.style.color = couponError
          ? '#ffd27d'
          : (couponApplied ? '#b7f7c5' : 'var(--gray-light)');
      }

      if (submitButton) {
        submitButton.disabled = Boolean(error) || @json(! $tapCheckoutAvailable || ! $cart || $cart->items->isEmpty());
      }
    } catch (error) {
      if (error.name === 'AbortError') {
        return;
      }

      if (summaryMessage) {
        summaryMessage.textContent = @json(__('storefront.checkout_summary_refresh_failed'));
        summaryMessage.classList.remove('hidden');
      }

      if (couponMessage) {
        couponMessage.textContent = couponHintText;
        couponMessage.style.color = 'var(--gray-light)';
      }

      if (submitButton) {
        submitButton.disabled = true;
      }
    } finally {
      setCouponLoadingState(false);
    }
  }

  function applyDetectedCountryFallback() {
    if (!countrySelect || countrySelect.value) {
      return;
    }

    const detectedCode = String(frontendPricingContext?.detected_country_code || '').toUpperCase();
    const detectedCountry = detectedCountryNameMap[detectedCode] || '';

    if (!detectedCountry) {
      return;
    }

    const optionExists = Array.from(countrySelect.options).some((option) => option.value === detectedCountry);

    if (optionExists) {
      countrySelect.value = detectedCountry;
    }
  }

  countrySelect?.addEventListener('change', refreshCheckoutSummary);
  emailInput?.addEventListener('blur', refreshCheckoutSummary);
  couponInput?.addEventListener('keydown', function (event) {
    if (event.key === 'Enter') {
      event.preventDefault();
      refreshCheckoutSummary();
    }
  });
  applyCouponButton?.addEventListener('click', refreshCheckoutSummary);
  applyDetectedCountryFallback();
  refreshCheckoutSummary();
});
</script>
<style>
  .checkout-coupon-status {
    min-width: 132px;
    opacity: 1;
  }

  .checkout-coupon-status:hover {
    color: var(--black);
  }

  .checkout-coupon-status:hover::before {
    transform: translateX(101%);
  }

  .checkout-coupon-status.is-loading {
    color: var(--white);
  }

  .checkout-coupon-status.is-loading::before {
    transform: translateX(0);
  }

  .checkout-coupon-status__loading {
    position: relative;
    z-index: 1;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  .checkout-coupon-status__loading:not([hidden]) {
    display: inline-flex;
  }

  .checkout-coupon-status__spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgb(var(--white-rgb) / 0.35);
    border-top-color: rgb(var(--white-rgb));
    border-radius: 999px;
    display: inline-block;
    animation: checkoutCouponSpin .8s linear infinite;
  }

@keyframes checkoutCouponSpin {
  to {
    transform: rotate(360deg);
  }
}
</style>
@endpush

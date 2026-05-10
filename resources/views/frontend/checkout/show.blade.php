@php
  $title = __('storefront.checkout');
  $summaryCurrency = $cart?->currency ?? 'BHD';
  $checkoutWhatsappPhone = preg_replace('/\D+/', '', (string) ($frontendBrand['whatsapp_phone'] ?? ''));
  $checkoutWhatsappMessage = rawurlencode(__('storefront.checkout_whatsapp_quote_message'));
@endphp

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
            <div class="footer-payment-icons mt-4" aria-label="Payment methods">
              @foreach (['visa', 'mastercard', 'amex', 'apple', 'google', 'samsung-pay', 'click-to-pay', 'benefit', 'benefit-pay'] as $paymentIcon)
                <img src="{{ asset('pay-icons/' . $paymentIcon . '.svg') }}" alt="" class="payment-icon" loading="lazy">
              @endforeach
            </div>
          </div>
        </form>
      </div>

      <aside class="border p-8 h-fit" style="border-color:var(--line-soft);background:var(--gray-dark)">
        <div class="space-y-6">
          <div>
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
                    @if ($item->display_variant_name)
                      <p class="text-sm" style="color:var(--gray-light)">{{ $item->display_variant_name }}</p>
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
                <div class="flex items-center justify-between gap-3 mb-3">
                  <label class="text-xs font-bold block checkout-label">{{ __('storefront.checkout_shipping_box_label') }}</label>
                </div>
                <div class="checkout-shipping-loading" id="checkoutShippingBoxLoading" hidden aria-hidden="true">
                  <span class="checkout-coupon-status__spinner"></span>
                </div>
                <div class="grid gap-3 grid-cols-2" id="checkoutShippingBoxGroup">
                  <label class="checkout-shipping-option" data-box-option>
                    <input
                      type="radio"
                      name="shipping_box_type"
                      value="with_box"
                      form="checkoutForm"
                      class="checkout-shipping-option__input"
                      @checked(($checkoutForm['shipping_box_type'] ?? 'without_box') === 'with_box')
                    >
                    <span class="checkout-shipping-option__content">
                      <span class="checkout-shipping-option__title">{{ __('storefront.checkout_shipping_with_box') }}</span>
                    </span>
                  </label>
                  <label class="checkout-shipping-option" data-box-option>
                    <input
                      type="radio"
                      name="shipping_box_type"
                      value="without_box"
                      form="checkoutForm"
                      class="checkout-shipping-option__input"
                      @checked(($checkoutForm['shipping_box_type'] ?? 'without_box') === 'without_box')
                    >
                    <span class="checkout-shipping-option__content">
                      <span class="checkout-shipping-option__title">{{ __('storefront.checkout_shipping_without_box') }}</span>
                    </span>
                  </label>
                </div>
                @error('shipping_box_type')<p class="mt-2 text-sm checkout-error">{{ $message }}</p>@enderror
              </div>

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
                <div
                  id="checkoutCouponMessage"
                  class="checkout-coupon-feedback mt-3 {{ $checkoutSummary['coupon_error'] || $checkoutSummary['coupon_applied'] ? ($checkoutSummary['coupon_error'] ? 'is-error' : 'is-success') : '' }}"
                >{{ $checkoutSummary['coupon_error'] ?: ($checkoutSummary['coupon_applied'] ? __('storefront.checkout_coupon_applied') : __('storefront.checkout_coupon_hint')) }}</div>
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
              @if ($checkoutWhatsappPhone)
                <a
                  href="https://wa.me/{{ $checkoutWhatsappPhone }}?text=مرحباً، أنا في صفحة إتمام الشراء وأحتاج مساعدة بخصوص تكلفة الطلب."
                  class="checkout-whatsapp-quote"
                  target="_blank"
                  rel="noreferrer"
                >
                  <span class="checkout-whatsapp-quote__icon">
                    <i class="bx bxl-whatsapp" aria-hidden="true"></i>
                  </span>
                  <span class="checkout-whatsapp-quote__body">
                    <strong>{{ __('storefront.checkout_whatsapp_quote_title') }}</strong>
                    <span>{{ __('storefront.checkout_whatsapp_quote_copy') }}</span>
                  </span>
                  <i class="bx bx-chevron-left checkout-whatsapp-quote__arrow" aria-hidden="true"></i>
                </a>
              @endif
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
  const shippingBoxInputs = document.querySelectorAll('input[name="shipping_box_type"]');
  const shippingBoxOptions = document.querySelectorAll('[data-box-option]');
  const shippingBoxGroup = document.getElementById('checkoutShippingBoxGroup');
  const shippingBoxLoading = document.getElementById('checkoutShippingBoxLoading');
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

  function selectedShippingBoxType() {
    return document.querySelector('input[name="shipping_box_type"]:checked')?.value || 'without_box';
  }

  function syncShippingBoxSelection() {
    shippingBoxOptions.forEach((option) => {
      const input = option.querySelector('input[name="shipping_box_type"]');
      option.classList.toggle('is-selected', Boolean(input?.checked));
    });
  }

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

  function setShippingBoxLoadingState(isLoading) {
    if (shippingBoxGroup) {
      shippingBoxGroup.style.display = isLoading ? 'none' : '';
    }

    if (shippingBoxLoading) {
      shippingBoxLoading.style.display = isLoading ? 'flex' : 'none';
    }
  }

  async function refreshCheckoutSummary(options = {}) {
    if (!countrySelect) {
      return;
    }

    const source = options.source || 'general';

    if (activeRequest) {
      activeRequest.abort();
    }

    activeRequest = new AbortController();

    if (source === 'coupon') {
      setCouponLoadingState(true);
    }

    if (source === 'shipping_box') {
      setShippingBoxLoadingState(true);
    }

    try {
      const query = new URLSearchParams({
        country: countrySelect.value || '',
        email: emailInput?.value || '',
        coupon_code: couponInput?.value || '',
        shipping_box_type: selectedShippingBoxType(),
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
      const currency = document.getElementById('checkoutGrandTotalPrice')?.dataset.bhdCurrency || 'BHD';
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
        couponMessage.classList.toggle('is-error', Boolean(couponError));
        couponMessage.classList.toggle('is-success', !couponError && couponApplied);
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
        couponMessage.classList.remove('is-error', 'is-success');
      }

      if (submitButton) {
        submitButton.disabled = true;
      }
    } finally {
      if (source === 'coupon') {
        setCouponLoadingState(false);
      }

      if (source === 'shipping_box') {
        setShippingBoxLoadingState(false);
      }
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

  countrySelect?.addEventListener('change', function () {
    refreshCheckoutSummary({ source: 'general' });
  });
  emailInput?.addEventListener('blur', function () {
    refreshCheckoutSummary({ source: 'general' });
  });
  shippingBoxInputs.forEach((input) => input.addEventListener('change', function () {
    syncShippingBoxSelection();
    refreshCheckoutSummary({ source: 'shipping_box' });
  }));
  couponInput?.addEventListener('keydown', function (event) {
    if (event.key === 'Enter') {
      event.preventDefault();
      refreshCheckoutSummary({ source: 'coupon' });
    }
  });
  applyCouponButton?.addEventListener('click', function () {
    refreshCheckoutSummary({ source: 'coupon' });
  });
  applyDetectedCountryFallback();
  syncShippingBoxSelection();
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

  .checkout-shipping-option {
    position: relative;
    border: 1px solid var(--line-soft);
    background: rgb(var(--white-rgb) / .03);
    cursor: pointer;
    transition: border-color .2s ease, background-color .2s ease, transform .2s ease, box-shadow .2s ease;
    min-height: 96px;
    display: block;
  }

  .checkout-shipping-option:hover {
    border-color: rgb(var(--white-rgb) / .28);
    background: rgb(var(--white-rgb) / .05);
  }

  .checkout-shipping-option.is-selected {
    border-color: rgb(var(--white-rgb) / .9);
    background: linear-gradient(180deg, rgb(var(--white-rgb) / .12), rgb(var(--white-rgb) / .05));
    box-shadow: 0 0 0 1px rgb(var(--white-rgb) / .18);
    transform: translateY(-1px);
  }

  .checkout-shipping-option__input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }

  .checkout-shipping-option__content {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    position: relative;
    min-height: 94px;
  }

  .checkout-shipping-option__body {
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
  }

  .checkout-shipping-option__title {
    font-weight: 800;
    display: block;
  }

  .checkout-shipping-option__copy {
    color: var(--gray-light);
    font-size: 12px;
    line-height: 1.6;
    display: block;
  }

  .checkout-shipping-loading {
    min-height: 94px;
    border: 1px solid var(--line-soft);
    background: rgb(var(--white-rgb) / .03);
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .checkout-shipping-loading[hidden] {
    display: none;
  }

  .checkout-coupon-feedback {
    border: 1px solid var(--line-soft);
    background: rgb(var(--white-rgb) / .03);
    padding: 12px 14px;
    font-size: 12px;
    line-height: 1.7;
    color: var(--gray-light);
  }

  .checkout-coupon-feedback.is-error {
    border-color: rgb(255 210 125 / .35);
    background: rgb(255 210 125 / .08);
    color: #ffd27d;
  }

  .checkout-coupon-feedback.is-success {
    border-color: rgb(183 247 197 / .28);
    background: rgb(183 247 197 / .08);
    color: #b7f7c5;
  }

  .checkout-whatsapp-quote {
    position: relative;
    display: grid;
    grid-template-columns: 46px minmax(0, 1fr) 22px;
    align-items: center;
    gap: 14px;
    padding: 16px;
    border: 1px solid rgb(37 211 102 / .38);
    background:
      linear-gradient(135deg, rgb(37 211 102 / .16), rgb(var(--white-rgb) / .035)),
      rgb(var(--white-rgb) / .025);
    color: var(--white);
    overflow: hidden;
    transition: border-color .2s ease, background-color .2s ease, transform .2s ease;
  }

  .checkout-whatsapp-quote::before {
    content: '';
    position: absolute;
    inset-inline-start: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #25d366;
  }

  .checkout-whatsapp-quote:hover {
    transform: translateY(-2px);
    border-color: rgb(37 211 102 / .72);
    background:
      linear-gradient(135deg, rgb(37 211 102 / .22), rgb(var(--white-rgb) / .055)),
      rgb(var(--white-rgb) / .035);
  }

  .checkout-whatsapp-quote__icon {
    width: 46px;
    height: 46px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #25d366;
    color: #06160c;
    font-size: 1.65rem;
    flex: none;
  }

  .checkout-whatsapp-quote__body {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 5px;
  }

  .checkout-whatsapp-quote__body strong {
    font-size: .94rem;
    font-weight: 900;
    line-height: 1.35;
  }

  .checkout-whatsapp-quote__body span {
    color: var(--gray-light);
    font-size: .82rem;
    line-height: 1.65;
  }

  .checkout-whatsapp-quote__arrow {
    color: #25d366;
    font-size: 1.35rem;
  }

  html[dir="ltr"] .checkout-whatsapp-quote__arrow {
    transform: rotate(180deg);
  }

@keyframes checkoutCouponSpin {
  to {
    transform: rotate(360deg);
  }
}
</style>
@endpush

@php($welcomeOffer = app(\App\Services\WelcomeCouponService::class)->describeCurrentOffer())

<div class="modal-overlay" id="welcomeCouponPopup">
  <div class="modal-box" style="max-width:560px;overflow:hidden">
    <button onclick="closeWelcomeCouponPopup()" style="position:absolute;top:16px;inset-inline-start:16px;color:var(--gray-light);z-index:10" type="button">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div style="padding:48px 40px;text-align:center;position:relative;z-index:1">
      <div style="width:80px;height:80px;background:var(--white);margin:0 auto 24px;display:flex;align-items:center;justify-content:center">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" style="stroke:var(--black)" stroke-width="1.5"><path d="M4 7h16v10H4z"/><path d="M8 11h8"/><path d="M8 15h5"/></svg>
      </div>
      <p class="text-xs font-bold mb-3" style="color:var(--gray-light);letter-spacing:0.3em">{{ __('storefront.welcome_coupon.eyebrow') }}</p>
      <h2 class="text-3xl font-black mb-3" style="letter-spacing:-0.02em">{{ __('storefront.welcome_coupon.title') }}<br><span style="color:var(--text-faint);-webkit-text-stroke:1px rgb(var(--white-rgb) / .5)">{{ $welcomeOffer['headline'] }}</span></h2>
      <p class="mb-2 leading-relaxed" style="color:var(--gray-light);font-size:15px">{{ __('storefront.welcome_coupon.copy') }}</p>
      <p class="mb-6 leading-relaxed" style="color:var(--text-faint);font-size:12px">{{ __('storefront.welcome_coupon.exclusive_updates') }}</p>

      <form id="welcomeCouponForm" class="grid gap-4 text-start" onsubmit="submitWelcomeCoupon(event)">
        <div>
          <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.auth.email') }}</label>
          <input type="email" class="input-field" id="welcomeCouponEmail" name="email" placeholder="{{ __('storefront.welcome_coupon.email_placeholder') }}" required>
        </div>
        <button class="btn-primary w-full" type="submit"><span>{{ __('storefront.welcome_coupon.cta') }}</span></button>
      </form>

      <p id="welcomeCouponStatus" class="text-sm mt-4" style="color:var(--gray-light)"></p>
      <button onclick="closeWelcomeCouponPopup()" class="text-sm mt-4 block w-full" style="color:var(--text-faint)" type="button">{{ __('storefront.welcome_coupon.dismiss') }}</button>
    </div>
  </div>
</div>

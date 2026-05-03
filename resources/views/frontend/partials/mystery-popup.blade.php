<div class="mystery-popup" id="mysteryPopup">
  <div class="mystery-box">
    <button onclick="closeMystery()" style="position:absolute;top:16px;inset-inline-start:16px;color:var(--gray-light);z-index:10">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div style="padding:48px 40px;text-align:center;position:relative;z-index:1">
      <div style="width:80px;height:80px;background:var(--white);margin:0 auto 24px;display:flex;align-items:center;justify-content:center">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" style="stroke:var(--black)" stroke-width="1.5"><rect x="3" y="8" width="18" height="13" rx="0"/><path d="M19 8V5.5A2.5 2.5 0 0 0 14 5.5V8"/><path d="M5 8V5.5A2.5 2.5 0 0 1 10 5.5V8"/><line x1="12" y1="8" x2="12" y2="21"/><line x1="3" y1="13" x2="21" y2="13"/></svg>
      </div>
      <p class="text-xs font-bold mb-3" style="color:var(--gray-light);letter-spacing:0.3em">{{ __('storefront.mystery.eyebrow') }}</p>
      <h2 class="text-3xl font-black mb-3" style="letter-spacing:-0.02em">{{ __('storefront.mystery.title') }}<br><span style="color:var(--text-faint);-webkit-text-stroke:1px rgb(var(--white-rgb) / .5)">{{ __('storefront.mystery.highlight') }}</span></h2>
      <p class="mb-6 leading-relaxed" style="color:var(--gray-light);font-size:15px">{!! __('storefront.mystery.copy', ['discount' => '<strong style="color:var(--white);font-size:20px">60%</strong>']) !!}</p>
      <div style="border:1px solid var(--line-soft);padding:20px;margin-bottom:24px;background:rgb(var(--white-rgb) / .02)">
        <p class="text-xs mb-2" style="color:var(--gray-light)">{{ __('storefront.mystery.coupon_label') }}</p>
        <p class="text-2xl font-black" style="letter-spacing:0.2em">MYSTERY60</p>
      </div>
      <button onclick="closeMystery()" class="btn-primary w-full"><span>{{ __('storefront.mystery.cta') }}</span></button>
      <button onclick="closeMystery()" class="text-sm mt-4 block w-full" style="color:var(--text-faint)">{{ __('storefront.mystery.dismiss') }}</button>
    </div>
    <div style="position:absolute;top:0;right:0;width:80px;height:80px;border-top:2px solid var(--line-mid);border-right:2px solid var(--line-mid)"></div>
    <div style="position:absolute;bottom:0;left:0;width:80px;height:80px;border-bottom:2px solid var(--line-mid);border-left:2px solid var(--line-mid)"></div>
  </div>
</div>

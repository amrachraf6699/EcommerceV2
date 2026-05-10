@php
  $footerPages = \App\Models\Page::query()->get();
  $hasCategoriesSection = $frontendNavCategories->isNotEmpty();
  $hasContactSection = filled($frontendBrand['address_ar'])
    || filled($frontendBrand['address_en'])
    || filled($frontendBrand['email'])
    || filled(setting('mail.mail_from_address'))
    || filled($frontendBrand['phone']);
  $hasPagesSection = $footerPages->isNotEmpty();
  $footerColumnCount = 2 + ($hasCategoriesSection ? 1 : 0) + ($hasContactSection ? 1 : 0) + ($hasPagesSection ? 1 : 0);
  $footerGridClasses = [
    2 => 'md:grid-cols-2',
    3 => 'md:grid-cols-3',
    4 => 'md:grid-cols-4',
    5 => 'md:grid-cols-5',
  ];
  $footerGridClass = $footerGridClasses[$footerColumnCount] ?? $footerGridClasses[2];
@endphp

<footer id="site-footer"
  style="background:rgb(var(--surface-rgb));border-top:1px solid var(--line-soft);margin-top:6px">
  <div class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid grid-cols-1 {{ $footerGridClass }} gap-12 mb-12">
      <div>
        <div class="flex items-center gap-3 mb-6">
          <div class="h-8 flex items-center justify-center overflow-hidden">
            @if ($frontendBrand['logo_url'])
              <img src="{{ $frontendBrand['logo_url'] }}" alt="{{ $frontendBrand['name'] }}"
                class="h-full object-cover">
            @else
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M2 18L6 6L12 14L16 8L22 18H2Z" style="fill:var(--black)" />
              </svg>
            @endif
          </div>
          <span class="text-xl font-black">{{ $frontendBrand['name'] }}</span>
        </div>
        <p class="text-sm mb-6 leading-relaxed" style="color:var(--gray-light)">{{ __('storefront.footer.about') }}</p>
        @php
          $icons = [
            'ÙÙŠØ³Ø¨ÙˆÙƒ' => 'bxl-facebook',
            'facebook' => 'bxl-facebook',
            'Ø¥Ù†Ø³ØªØºØ±Ø§Ù…' => 'bxl-instagram',
            'instagram' => 'bxl-instagram',
            'Ø³Ù†Ø§Ø¨ Ø´Ø§Øª' => 'bxl-snapchat',
            'snapchat' => 'bxl-snapchat',
            'ØªÙŠÙƒ ØªÙˆÙƒ' => 'bxl-tiktok',
            'tiktok' => 'bxl-tiktok',
            'Ø¥ÙƒØ³' => 'bxl-twitter',
            'twitter' => 'bxl-twitter',
            'x' => 'bxl-twitter',
          ];
        @endphp
        <div class="flex gap-3">
          @foreach ($frontendSocialLinks as $network => $url)
            @php
              $key = strtolower(trim($network));
              $icon = $icons[$network] ?? $icons[$key] ?? 'bx-link';
            @endphp
            <a href="{{ $url }}" class="social-icon flex items-center justify-center" target="_blank" rel="noreferrer"
              aria-label="{{ $network }}">
              <i class="bx {{ $icon }} text-xl"></i>
            </a>
          @endforeach
        </div>
      </div>
      <div>
        <h4 class="font-bold mb-6 text-sm" style="letter-spacing:0.15em">{{ __('storefront.footer.quick_links') }}</h4>
        <div class="flex flex-col gap-3">
          <a href="{{ route('storefront.home') }}" class="footer-link">{{ __('storefront.common.home') }}</a>
          @if ($hasCategoriesSection)
            <a href="{{ route('storefront.categories.show', ['category' => $frontendNavCategories->first()->slug]) }}"
              class="footer-link">{{ __('storefront.common.categories') }}</a>
          @endif
          <a href="{{ route('storefront.catalog') }}" class="footer-link">{{ __('storefront.common.products') }}</a>
          <a href="{{ route('storefront.catalog', ['sort' => 'featured']) }}"
            class="footer-link">{{ __('storefront.common.offers') }}</a>
          <a href="{{ route('storefront.contact.show') }}" class="footer-link">{{ __('storefront.common.contact') }}</a>
        </div>
      </div>
      @if ($hasCategoriesSection)
        <div>
          <h4 class="font-bold mb-6 text-sm" style="letter-spacing:0.15em">{{ __('storefront.footer.categories') }}</h4>
          <div class="flex flex-col gap-3">
            @foreach ($frontendNavCategories as $category)
              <a href="{{ route('storefront.categories.show', ['category' => $category->slug]) }}"
                class="footer-link">{{ $category->name }}</a>
            @endforeach
          </div>
        </div>
      @endif
      @if ($hasContactSection)
        <div>
          <h4 class="font-bold mb-6 text-sm" style="letter-spacing:0.15em">{{ __('storefront.footer.contact') }}</h4>
          <div class="flex flex-col gap-4">
            @if ($frontendBrand['address_ar'] || $frontendBrand['address_en'])
              <div class="flex items-start gap-3">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                  style="color:var(--gray-light);flex-shrink:0;margin-top:2px">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                  <circle cx="12" cy="10" r="3" />
                </svg>
                <span class="footer-link text-sm">
                  {{ app()->getLocale() === 'ar'
                      ? ($frontendBrand['address_ar'] ?? $frontendBrand['address_en'])
                      : ($frontendBrand['address_en'] ?? $frontendBrand['address_ar']) }}
                </span>
              </div>
            @endif

            @if ($frontendBrand['email'] ?: setting('mail.mail_from_address'))
              <div class="flex items-center gap-3">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                  style="color:var(--gray-light)">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                  <polyline points="22,6 12,13 2,6" />
                </svg>
                <a href="mailto:{{ $frontendBrand['email'] ?: setting('mail.mail_from_address', 'info@example.com') }}"
                  class="footer-link text-sm">
                  {{ $frontendBrand['email'] ?: setting('mail.mail_from_address', 'info@example.com') }}
                </a>
              </div>
            @endif

            @if ($frontendBrand['phone'])
              <div class="flex items-center gap-3">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                  style="color:var(--gray-light)">
                  <path
                    d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 3.08 5.18 2 2 0 0 1 5.06 3h3a2 2 0 0 1 2 1.72c.12.9.33 1.77.62 2.61a2 2 0 0 1-.45 2.11L9 10.67a16 16 0 0 0 4.33 4.33l1.23-1.23a2 2 0 0 1 2.11-.45c.84.29 1.71.5 2.61.62A2 2 0 0 1 22 16.92z" />
                </svg>
                <a href="tel:{{ $frontendBrand['phone'] }}" class="footer-link text-sm">{{ $frontendBrand['phone'] }}</a>
              </div>
            @endif
          </div>
        </div>
      @endif
      @if ($hasPagesSection)
        <div>
          <h4 class="font-bold mb-6 text-sm" style="letter-spacing:0.15em">{{ __('storefront.footer.know_more') }}</h4>
          <div class="flex flex-col gap-3">
            @foreach ($footerPages as $page)
              <a href="{{ route('storefront.pages.show', $page) }}" class="footer-link">{{ $page->title }}</a>
            @endforeach
          </div>
        </div>
      @endif
    </div>
    <div class="footer-meta">
      <div class="footer-meta__top">
        <p class="footer-copyright">
          &copy; {{ now()->year }} {{ $frontendBrand['name'] }}. {{ __('storefront.footer.rights') }}
        </p>
        <span class="footer-powered">
          Powered by
          <a href="https://wa.me/201063153994" target="_blank" rel="noopener noreferrer" class="footer-credit__link">
            Amr Achraf
          </a>
        </span>
      </div>

      <div class="footer-payments" aria-label="Payment methods">
        <div class="footer-payment-icons" aria-hidden="true">
          @foreach (['visa', 'mastercard', 'amex', 'apple', 'google', 'samsung-pay', 'click-to-pay', 'benefit', 'benefit-pay'] as $paymentIcon)
            <img src="{{ asset('pay-icons/' . $paymentIcon . '.svg') }}" alt="" class="payment-icon" loading="lazy">
          @endforeach
        </div>
      </div>
    </div>
  </div>
</footer>

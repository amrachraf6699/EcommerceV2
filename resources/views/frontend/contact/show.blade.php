@php($title = __('storefront.contact.title'))

@extends('frontend.layouts.app')

@section('content')
<section class="mt-4 px-6 pb-20" style="padding-top:120px">
  <div class="max-w-6xl mx-auto grid gap-8 lg:grid-cols-[minmax(0,1.15fr)_minmax(320px,.85fr)]">
    <div class="border p-8 md:p-10" style="border-color:var(--line-soft);background:var(--gray-dark)">
      <div class="max-w-2xl">
        <div class="divider reveal"></div>
        <h1 class="text-3xl md:text-4xl font-black mb-3">{{ __('storefront.contact.title') }}</h1>
        <p style="color:var(--gray-light)">{{ __('storefront.contact.copy') }}</p>
      </div>

      <form method="POST" action="{{ route('storefront.contact.store') }}" class="grid gap-5 md:grid-cols-2 mt-8">
        @csrf
        <div>
          <label class="block text-sm font-bold mb-3">{{ __('storefront.contact.name') }}</label>
          <input type="text" name="name" class="input-field" value="{{ old('name') }}" required>
          @error('name')<p class="mt-2 text-sm" style="color:#ffb2b2">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm font-bold mb-3">{{ __('storefront.contact.email') }}</label>
          <input type="email" name="email" class="input-field" value="{{ old('email') }}" required>
          @error('email')<p class="mt-2 text-sm" style="color:#ffb2b2">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm font-bold mb-3">{{ __('storefront.contact.phone') }}</label>
          <input type="text" name="phone" class="input-field" value="{{ old('phone') }}">
          @error('phone')<p class="mt-2 text-sm" style="color:#ffb2b2">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm font-bold mb-3">{{ __('storefront.contact.subject') }}</label>
          <input type="text" name="subject" class="input-field" value="{{ old('subject') }}" required>
          @error('subject')<p class="mt-2 text-sm" style="color:#ffb2b2">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-bold mb-3">{{ __('storefront.contact.message') }}</label>
          <textarea name="message" rows="7" class="input-field" required>{{ old('message') }}</textarea>
          @error('message')<p class="mt-2 text-sm" style="color:#ffb2b2">{{ $message }}</p>@enderror
        </div>
        @if ($recaptchaSiteKey)
          <div class="md:col-span-2">
            <div class="g-recaptcha" data-sitekey="{{ $recaptchaSiteKey }}"></div>
            @error('g-recaptcha-response')<p class="mt-2 text-sm" style="color:#ffb2b2">{{ $message }}</p>@enderror
          </div>
        @endif
        <div class="md:col-span-2">
          <button type="submit" class="btn-primary"><span>{{ __('storefront.contact.cta') }}</span></button>
        </div>
      </form>
    </div>

    <aside class="space-y-6">
      <div class="border p-8" style="border-color:var(--line-soft);background:var(--gray-dark)">
        <p class="text-xs font-bold mb-2" style="letter-spacing:0.14em;color:var(--gray-light)">{{ __('storefront.contact.direct_label') }}</p>
        <h2 class="text-2xl font-black mb-6">{{ __('storefront.contact.direct_title') }}</h2>
        <div class="space-y-4 text-sm" style="color:var(--gray-light)">
          @if ($frontendBrand['email'] ?: setting('mail.mail_from_address'))
            <div class="flex items-start gap-3">
              <i class="bx bx-envelope mt-1 inline-flex h-6 w-6 shrink-0 items-center justify-center text-lg" style="color:var(--white)" aria-hidden="true"></i>
              <div class="min-w-0 flex-1 text-start">
                <p class="mb-1 font-bold" style="color:var(--white)">{{ __('storefront.contact.email') }}</p>
                <a href="mailto:{{ $frontendBrand['email'] ?: setting('mail.mail_from_address') }}" class="footer-link">{{ $frontendBrand['email'] ?: setting('mail.mail_from_address') }}</a>
              </div>
            </div>
          @endif

          @if ($frontendBrand['phone'])
            <div class="flex items-start gap-3">
              <i class="bx bx-mobile mt-1 inline-flex h-6 w-6 shrink-0 items-center justify-center text-lg" style="color:var(--white)" aria-hidden="true"></i>
              <div class="min-w-0 flex-1 text-start">
                <p class="mb-1 font-bold" style="color:var(--white)">{{ __('storefront.contact.mobile') }}</p>
                <a href="tel:{{ $frontendBrand['phone'] }}" class="footer-link">{{ $frontendBrand['phone'] }}</a>
              </div>
            </div>
          @endif

          @if (app()->getLocale() === 'ar' ? ($frontendBrand['address_ar'] ?: $frontendBrand['address_en']) : ($frontendBrand['address_en'] ?: $frontendBrand['address_ar']))
            <div class="flex items-start gap-3">
              <i class="bx bx-map mt-1 inline-flex h-6 w-6 shrink-0 items-center justify-center text-lg" style="color:var(--white)" aria-hidden="true"></i>
              <div class="min-w-0 flex-1 text-start">
                <p class="mb-1 font-bold" style="color:var(--white)">{{ __('storefront.contact.address') }}</p>
                <p>{{ app()->getLocale() === 'ar' ? ($frontendBrand['address_ar'] ?: $frontendBrand['address_en']) : ($frontendBrand['address_en'] ?: $frontendBrand['address_ar']) }}</p>
              </div>
            </div>
          @endif

          @if ($frontendBrand['working_hours'])
            <div class="flex items-start gap-3">
              <i class="bx bx-time-five mt-1 inline-flex h-6 w-6 shrink-0 items-center justify-center text-lg" style="color:var(--white)" aria-hidden="true"></i>
              <div class="min-w-0 flex-1 text-start">
                <p class="mb-1 font-bold" style="color:var(--white)">{{ __('storefront.contact.working_hours') }}</p>
                <p>{{ $frontendBrand['working_hours'] }}</p>
              </div>
            </div>
          @endif

          @if ($frontendBrand['country'])
            <div class="flex items-start gap-3">
              <i class="bx bx-globe mt-1 inline-flex h-6 w-6 shrink-0 items-center justify-center text-lg" style="color:var(--white)" aria-hidden="true"></i>
              <div class="min-w-0 flex-1 text-start">
                <p class="mb-1 font-bold" style="color:var(--white)">{{ __('storefront.contact.country') }}</p>
                <p>{{ $frontendBrand['country'] }}</p>
              </div>
            </div>
          @endif

          @if ($frontendBrand['cr_number'])
            <div class="flex items-start gap-3">
              <i class="bx bx-id-card mt-1 inline-flex h-6 w-6 shrink-0 items-center justify-center text-lg" style="color:var(--white)" aria-hidden="true"></i>
              <div class="min-w-0 flex-1 text-start">
                <p class="mb-1 font-bold" style="color:var(--white)">{{ __('storefront.contact.cr_number') }}</p>
                <p>{{ $frontendBrand['cr_number'] }}</p>
              </div>
            </div>
          @endif
        </div>
      </div>
    </aside>
  </div>
</section>
@endsection

@if ($recaptchaSiteKey)
  @push('scripts')
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  @endpush
@endif

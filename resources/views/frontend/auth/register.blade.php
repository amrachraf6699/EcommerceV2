@php($title = __('storefront.auth.register'))

@extends('frontend.layouts.app')

@section('content')
<section class="px-6 pb-20" style="padding-top:120px">
  <div class="max-w-3xl mx-auto border p-8 md:p-10" style="border-color:var(--line-soft);background:var(--gray-dark)">
    <div class="mb-8 text-center">
      <div class="divider" style="margin:0 auto 16px"></div>
      <h1 class="text-3xl font-black mb-3">{{ __('storefront.auth.register') }}</h1>
      <p style="color:var(--gray-light)">{{ __('storefront.auth.register_copy') }}</p>
    </div>

    <form method="POST" action="{{ route('storefront.auth.register.store') }}" class="grid gap-5 md:grid-cols-2">
      @csrf

      <div class="md:col-span-2">
        <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.account.name') }}</label>
        <input type="text" name="name" class="input-field" value="{{ old('name') }}" required>
        @error('name')<p class="mt-2 text-sm" style="color:#ffb2b2">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.auth.email') }}</label>
        <input type="email" name="email" class="input-field" value="{{ old('email') }}" required>
        @error('email')<p class="mt-2 text-sm" style="color:#ffb2b2">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.account.phone') }}</label>
        <input type="text" name="phone" class="input-field" value="{{ old('phone') }}">
        @error('phone')<p class="mt-2 text-sm" style="color:#ffb2b2">{{ $message }}</p>@enderror
      </div>

      <div class="md:col-span-2">
        <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.account.country') }}</label>
        <select name="country" class="sort-select country-select">
          @include('frontend.partials.country-options', ['selectedCountry' => old('country')])
        </select>
        @error('country')<p class="mt-2 text-sm" style="color:#ffb2b2">{{ $message }}</p>@enderror
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
        <button type="submit" class="btn-primary w-full"><span>{{ __('storefront.auth.register') }}</span></button>
      </div>
    </form>

    <p class="mt-6 text-center text-sm" style="color:var(--gray-light)">
      {{ __('storefront.auth.have_account') }}
      <a href="{{ route('storefront.auth.login') }}" style="color:var(--white);font-weight:700">{{ __('storefront.auth.login') }}</a>
    </p>
  </div>
</section>
@endsection

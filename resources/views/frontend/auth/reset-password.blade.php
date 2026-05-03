@php($title = __('storefront.auth.reset_password'))

@extends('frontend.layouts.app')

@section('content')
<section class="px-6 pb-20" style="padding-top:120px">
  <div class="max-w-2xl mx-auto border p-8 md:p-10" style="border-color:var(--line-soft);background:var(--gray-dark)">
    <div class="mb-8 text-center">
      <div class="divider" style="margin:0 auto 16px"></div>
      <h1 class="text-3xl font-black mb-3">{{ __('storefront.auth.reset_password') }}</h1>
      <p style="color:var(--gray-light)">{{ __('storefront.auth.reset_password_copy') }}</p>
    </div>

    <form method="POST" action="{{ route('storefront.auth.password.update') }}" class="space-y-5">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">

      <div>
        <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.auth.email') }}</label>
        <input type="email" name="email" class="input-field" value="{{ old('email', $request->email) }}" required>
        @error('email')<p class="mt-2 text-sm" style="color:#ffb2b2">{{ $message }}</p>@enderror
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

      <button type="submit" class="btn-primary w-full"><span>{{ __('storefront.auth.reset_password') }}</span></button>
    </form>
  </div>
</section>
@endsection

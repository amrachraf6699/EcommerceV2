@php($title = __('storefront.auth.forgot_password'))

@extends('frontend.layouts.app')

@section('content')
<section class="px-6 pb-20" style="padding-top:120px">
  <div class="max-w-2xl mx-auto border p-8 md:p-10" style="border-color:var(--line-soft);background:var(--gray-dark)">
    <div class="mb-8 text-center">
      <div class="divider" style="margin:0 auto 16px"></div>
      <h1 class="text-3xl font-black mb-3">{{ __('storefront.auth.reset_title') }}</h1>
      <p style="color:var(--gray-light)">{{ __('storefront.auth.reset_copy') }}</p>
    </div>

    @if (session('success'))
      <div class="mb-5 border px-4 py-3 text-sm" style="border-color:var(--line-mid);background:rgb(var(--white-rgb) / .04)">
        {{ session('success') }}
      </div>
    @endif

    <form method="POST" action="{{ route('storefront.auth.password.email') }}" class="space-y-5">
      @csrf
      <div>
        <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.auth.email') }}</label>
        <input type="email" name="email" class="input-field" value="{{ old('email') }}" required>
        @error('email')<p class="mt-2 text-sm" style="color:#ffb2b2">{{ $message }}</p>@enderror
      </div>
      <button type="submit" class="btn-primary w-full"><span>{{ __('storefront.auth.send_reset_link') }}</span></button>
    </form>
  </div>
</section>
@endsection

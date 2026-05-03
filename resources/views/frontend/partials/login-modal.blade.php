<div class="modal-overlay {{ $errors->has('email') && session('auth_modal_tab') === 'login' ? 'open' : '' }}" id="loginModal">
  <div class="modal-box">
    <div style="padding:40px">
      <div class="flex items-center justify-between mb-8">
        <div>
          <h2 class="text-2xl font-black mb-1">{{ __('storefront.auth.welcome') }}</h2>
          <p class="text-sm" style="color:var(--gray-light)">{{ __('storefront.auth.subtitle') }}</p>
        </div>
        <button onclick="closeLoginModal()" style="color:var(--gray-light)">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      @if ($errors->has('email') && session('auth_modal_tab') === 'login')
        <div class="mb-5 border px-4 py-3 text-sm" style="border-color:rgba(255,99,99,.4);background:rgba(255,99,99,.08);color:#ffb2b2">
          {{ $errors->first('email') }}
        </div>
      @endif
      <form class="flex flex-col gap-4" method="POST" action="{{ route('storefront.auth.login.store') }}">
        @csrf
        <div>
          <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.auth.email') }}</label>
          <input type="email" name="email" class="input-field" placeholder="example@email.com" value="{{ old('email') }}" required>
        </div>
        <div>
          <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.auth.password') }}</label>
          <input type="password" name="password" class="input-field" placeholder="••••••••" required>
        </div>
        <div class="flex justify-between items-center">
          <label class="flex items-center gap-2 text-sm" style="color:var(--gray-light)">
            <input type="checkbox" name="remember" value="1" style="accent-color:white" @checked(old('remember'))> {{ __('storefront.auth.remember') }}
          </label>
          <a href="{{ route('storefront.auth.password.request') }}" class="text-sm" style="color:var(--white)">{{ __('storefront.auth.forgot_password') }}</a>
        </div>
        <button type="submit" class="btn-primary w-full mt-2">
          <span>{{ __('storefront.auth.login') }}</span>
        </button>
        <div style="border-top:1px solid rgba(245,245,240,0.1);padding-top:20px;text-align:center">
          <p class="text-sm" style="color:var(--gray-light)">{{ __('storefront.auth.no_account') }} <a href="{{ route('storefront.auth.register') }}" style="color:var(--white);font-weight:700">{{ __('storefront.auth.create_account') }}</a></p>
        </div>
      </form>
    </div>
    <div style="position:absolute;bottom:0;right:0;width:120px;height:120px;background:repeating-linear-gradient(45deg,rgba(255,255,255,0.02),rgba(255,255,255,0.02) 2px,transparent 2px,transparent 8px);pointer-events:none"></div>
  </div>
</div>

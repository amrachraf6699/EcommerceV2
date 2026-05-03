@php
    $title = __('storefront.cart_title');
@endphp

@extends('frontend.layouts.app')

@section('content')
<style>
  .cart-shell { display:grid; gap:1rem; }
  .cart-card { border:1px solid var(--line-soft); background:
    radial-gradient(circle at top right, rgb(var(--white-rgb) / .08), transparent 28%),
    linear-gradient(180deg, rgb(var(--white-rgb) / .05), rgb(var(--white-rgb) / .02));
    box-shadow:0 18px 34px rgb(var(--shadow-rgb) / .12);
  }
  .cart-thumb { border:1px solid var(--line-soft); background:linear-gradient(135deg,rgb(var(--surface-rgb)) 0%,rgb(var(--surface-alt-rgb)) 100%); }
  .cart-chip { display:inline-flex; align-items:center; gap:.45rem; padding:.45rem .7rem; font-size:.73rem; font-weight:800; letter-spacing:.04em; background:rgb(var(--white-rgb) / .06); color:var(--gray-light); }
  .cart-chip--danger { background:rgb(255 120 120 / .08); color:#ffb2b2; }
  .cart-metric { border:1px solid var(--line-soft); background:rgb(var(--white-rgb) / .03); }
  .cart-icon-button { width:2.85rem; height:2.85rem; display:inline-flex; align-items:center; justify-content:center; border:1px solid var(--line-soft); background:rgb(var(--white-rgb) / .03); color:var(--gray-light); transition:transform .2s ease, background-color .2s ease, color .2s ease, border-color .2s ease; }
  .cart-icon-button:hover { transform:translateY(-1px); background:var(--white); color:var(--black); border-color:var(--white); }
  .cart-stepper { display:inline-grid; grid-template-columns:44px minmax(56px,auto) 44px; align-items:center; border:1px solid var(--line-soft); background:rgb(var(--white-rgb) / .03); }
  .cart-stepper__button { width:44px; height:44px; display:inline-flex; align-items:center; justify-content:center; background:transparent; color:var(--white); transition:background-color .2s ease, color .2s ease; }
  .cart-stepper__button:hover { background:rgb(var(--white-rgb) / .1); }
  .cart-stepper__value { width:100%; min-width:56px; padding:0 8px; text-align:center; background:transparent; color:var(--white); font-size:1rem; font-weight:900; outline:none; border-left:1px solid var(--line-soft); border-right:1px solid var(--line-soft); }
  .cart-submit { width:2.85rem; height:2.85rem; display:inline-flex; align-items:center; justify-content:center; border:1px solid var(--white); background:var(--white); color:var(--black); transition:transform .2s ease, background-color .2s ease, color .2s ease; }
  .cart-submit:hover { transform:translateY(-1px); background:transparent; color:var(--white); }
  .cart-summary-card { border:1px solid var(--line-soft); background:linear-gradient(180deg, rgb(var(--white-rgb) / .06), rgb(var(--white-rgb) / .025)); box-shadow:0 22px 40px rgb(var(--shadow-rgb) / .14); }
  .cart-summary-row { border:1px solid var(--line-soft); background:rgb(var(--white-rgb) / .03); }
  @media (min-width:1024px) {
    .cart-shell { grid-template-columns:minmax(0,1fr) 340px; gap:1.5rem; }
  }
  @media (min-width:1280px) {
    .cart-shell { grid-template-columns:minmax(0,1fr) 380px; }
  }
  @media (max-width:767px) {
    .cart-card,
    .cart-summary-card { box-shadow:none; }
  }
</style>

<section class="px-4 md:px-6 pb-24" style="padding-top:120px">
  <div class="max-w-7xl mx-auto space-y-6 md:space-y-8">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
      <div class="max-w-2xl">
        <div class="divider reveal"></div>
        <h1 class="text-3xl md:text-5xl font-black mt-3">{{ __('storefront.cart_title') }}</h1>
        <p class="mt-3 text-sm md:text-base" style="color:var(--gray-light)">{{ __('storefront.cart_copy') }}</p>
      </div>

      @if ($cart && $cart->items->isNotEmpty())
        <div class="inline-flex items-center gap-3 self-start px-4 py-3 border" style="border-color:var(--line-soft);background:var(--gray-dark)">
          <span class="inline-flex items-center justify-center w-10 h-10" style="background:rgb(var(--white-rgb) / .06)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
              <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
              <line x1="3" y1="6" x2="21" y2="6"/>
              <path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
          </span>
          <div class="space-y-1">
            <p class="text-[11px] font-black uppercase tracking-[0.18em]" style="color:var(--gray-light)">{{ __('storefront.cart_items_label') }}</p>
            <p class="text-lg font-black">{{ $cart->item_count }}</p>
          </div>
        </div>
      @endif
    </div>

    @if ($errors->any())
      <div class="checkout-notice checkout-notice--error">
        {{ $errors->first() }}
      </div>
    @endif

    @if (! $cart || $cart->items->isEmpty())
      <div class="border p-6 md:p-10 text-center space-y-5" style="border-color:var(--line-soft);background:var(--gray-dark)">
        <div class="mx-auto w-16 h-16 flex items-center justify-center border" style="border-color:var(--line-soft);background:rgb(var(--white-rgb) / .04)">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 0 1-8 0"/>
          </svg>
        </div>
        <h2 class="text-2xl md:text-3xl font-black">{{ __('storefront.cart_empty') }}</h2>
        <p class="max-w-xl mx-auto text-sm md:text-base" style="color:var(--gray-light)">{{ __('storefront.checkout_empty_cart') }}</p>
        <div class="flex flex-wrap items-center justify-center gap-3">
          <a href="{{ route('storefront.catalog') }}" class="btn-primary"><span>{{ __('storefront.cart_continue_shopping') }}</span></a>
          <a href="{{ route('storefront.categories.index') }}" class="btn-outline"><span>{{ __('storefront.common.categories') }}</span></a>
        </div>
      </div>
    @else
      <div class="cart-shell">
        <div class="space-y-4">
          @foreach ($cart->items as $item)
            @php
              $product = $item->product;
              $variant = $item->variant;
              $productUrl = $product && $product->slug ? route('storefront.products.show', ['product' => $product->slug]) : null;
              $primaryImage = $product?->images?->sortByDesc('is_primary')->sortBy('sort_order')->first();
              $imageUrl = $primaryImage?->path ? asset('storage/' . $primaryImage->path) : null;
              $stockQuantity = (int) ($variant?->stock_quantity ?? 0);
              $isPurchasable = $product?->is_active && $variant?->is_active && $stockQuantity > 0;
            @endphp

            <article class="cart-card overflow-hidden">
              <div class="p-4 md:p-5">
                <div class="flex gap-4">
                  <div class="cart-thumb w-24 h-24 md:w-28 md:h-28 shrink-0 overflow-hidden">
                    @if ($imageUrl)
                      <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover" loading="lazy">
                    @else
                      <div class="w-full h-full flex items-center justify-center text-[10px]" style="color:var(--gray-light)">{{ __('storefront.common.no_image') }}</div>
                    @endif
                  </div>

                  <div class="min-w-0 flex-1 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                      <div class="min-w-0">
                        @if ($productUrl)
                          <a href="{{ $productUrl }}" class="block text-lg md:text-xl font-black leading-tight">{{ $item->product_name }}</a>
                        @else
                          <h2 class="text-lg md:text-xl font-black leading-tight">{{ $item->product_name }}</h2>
                        @endif

                        @if ($item->variant_name)
                          <p class="mt-2 cart-chip">{{ $item->variant_name }}</p>
                        @endif
                      </div>

                      <form method="POST" action="{{ route('storefront.cart.items.destroy', ['item' => $item->id]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="cart-icon-button" aria-label="{{ __('storefront.cart_remove') }}">
                          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <path d="M3 6h18"/>
                            <path d="M8 6V4h8v2"/>
                            <path d="M19 6l-1 14H6L5 6"/>
                            <path d="M10 11v6"/>
                            <path d="M14 11v6"/>
                          </svg>
                        </button>
                      </form>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 text-xs md:text-sm" style="color:var(--gray-light)">
                      @if (! $isPurchasable)
                        <span class="cart-chip cart-chip--danger">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 8v5"/>
                            <path d="M12 16h.01"/>
                          </svg>
                          <span>{{ __('storefront.cart_variant_unavailable') }}</span>
                        </span>
                      @elseif ($stockQuantity > 0)
                        <span class="cart-chip">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M20 7 9 18l-5-5"/>
                          </svg>
                          <span>{{ __('storefront.cart_stock_available', ['count' => $stockQuantity]) }}</span>
                        </span>
                      @endif
                    </div>
                  </div>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
                  <div class="grid grid-cols-2 gap-3">
                    <div class="cart-metric p-3">
                      <div class="inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.16em]" style="color:var(--gray-light)">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                          <circle cx="12" cy="12" r="9"/>
                          <path d="M12 7v5l3 3"/>
                        </svg>
                        <span>{{ __('storefront.cart_unit_price') }}</span>
                      </div>
                      <div class="mt-2">
                        <x-frontend.price
                          :amount="$item->unit_price"
                          :currency="$cart->currency"
                          wrapper-class="items-start text-left gap-1"
                          amount-class="font-black text-base md:text-lg"
                          row-class="flex items-center gap-2"
                        />
                      </div>
                    </div>

                    <div class="cart-metric p-3">
                      <div class="inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.16em]" style="color:var(--gray-light)">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                          <path d="M4 12h16"/>
                          <path d="M12 4v16"/>
                        </svg>
                        <span>{{ __('storefront.cart_line_total') }}</span>
                      </div>
                      <div class="mt-2">
                        <x-frontend.price
                          :amount="$item->line_total"
                          :currency="$cart->currency"
                          wrapper-class="items-start text-left gap-1"
                          amount-class="font-black text-lg md:text-xl"
                          row-class="flex items-center gap-2"
                        />
                      </div>
                    </div>
                  </div>

                  <form method="POST" action="{{ route('storefront.cart.items.update', ['item' => $item->id]) }}" class="flex items-center gap-2 md:justify-end">
                    @csrf
                    @method('PATCH')
                    <label class="sr-only" for="cart-quantity-{{ $item->id }}">{{ __('storefront.common.quantity') }}</label>
                    <div class="cart-stepper" data-cart-stepper>
                      <button type="button" class="cart-stepper__button" data-step="-1" aria-label="Decrease quantity">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M5 12h14"/>
                        </svg>
                      </button>
                      <input
                        id="cart-quantity-{{ $item->id }}"
                        type="number"
                        name="quantity"
                        min="1"
                        @if ($stockQuantity > 0) max="{{ $stockQuantity }}" @endif
                        value="{{ old('quantity', $item->quantity) }}"
                        class="cart-stepper__value"
                      >
                      <button type="button" class="cart-stepper__button" data-step="1" aria-label="Increase quantity">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M12 5v14"/>
                          <path d="M5 12h14"/>
                        </svg>
                      </button>
                    </div>

                    <button type="submit" class="cart-submit" aria-label="{{ __('storefront.cart_update') }}">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M21 12a9 9 0 1 1-2.64-6.36"/>
                        <path d="M21 3v6h-6"/>
                      </svg>
                    </button>
                  </form>
                </div>
              </div>
            </article>
          @endforeach
        </div>

        <aside class="cart-summary-card p-5 md:p-6 h-fit lg:sticky lg:top-[132px] space-y-5">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-[11px] font-black uppercase tracking-[0.18em]" style="color:var(--gray-light)">{{ __('storefront.cart_summary') }}</p>
              <h2 class="text-2xl font-black mt-2">{{ __('storefront.account.order_summary') }}</h2>
            </div>
            <span class="inline-flex items-center justify-center w-12 h-12 border" style="border-color:var(--line-soft);background:rgb(var(--white-rgb) / .04)">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 0 1-8 0"/>
              </svg>
            </span>
          </div>

          <div class="grid gap-3">
            <div class="cart-summary-row p-4 flex items-center justify-between gap-4">
              <div class="inline-flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10" style="background:rgb(var(--white-rgb) / .06)">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                  </svg>
                </span>
                <span class="text-sm font-bold" style="color:var(--gray-light)">{{ __('storefront.cart_items_label') }}</span>
              </div>
              <strong class="text-xl font-black">{{ $cart->item_count }}</strong>
            </div>

            <div class="cart-summary-row p-4 space-y-2">
              <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10" style="background:rgb(var(--white-rgb) / .06)">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 5v14"/>
                    <path d="M5 12h14"/>
                  </svg>
                </span>
                <span class="text-sm font-bold" style="color:var(--gray-light)">{{ __('storefront.account.subtotal') }}</span>
              </div>
              <div class="ps-[52px]">
                <x-frontend.price
                  :amount="$cart->subtotal"
                  :currency="$cart->currency"
                  wrapper-class="items-start text-left gap-1"
                  amount-class="font-black text-2xl md:text-3xl"
                  row-class="flex items-center gap-2"
                />
              </div>
            </div>
          </div>

          <div class="space-y-3">
            <a href="{{ route('storefront.checkout.show') }}" class="btn-primary w-full"><span>{{ __('storefront.checkout') }}</span></a>
            <a href="{{ route('storefront.catalog') }}" class="btn-outline w-full"><span>{{ __('storefront.cart_continue_shopping') }}</span></a>
          </div>
        </aside>
      </div>
    @endif
  </div>
</section>

<script>
  document.querySelectorAll('[data-cart-stepper]').forEach((stepper) => {
    const input = stepper.querySelector('.cart-stepper__value');

    if (!input) {
      return;
    }

    stepper.querySelectorAll('[data-step]').forEach((button) => {
      button.addEventListener('click', () => {
        const diff = Number(button.getAttribute('data-step') || 0);
        const min = Number(input.getAttribute('min') || 1);
        const max = Number(input.getAttribute('max') || 99999);
        const current = Number(input.value || min);
        const next = Math.min(max, Math.max(min, current + diff));
        input.value = String(next);
      });
    });
  });
</script>
@endsection

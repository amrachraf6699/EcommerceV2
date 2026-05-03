@php
  $brandName = $frontendBrand['name'] ?? config('app.name');
  $mainImage = $product->images->sortByDesc('is_primary')->sortBy('sort_order')->first();
  $mainImageUrl = $mainImage ? asset('storage/' . $mainImage->path) : $product->primary_image_url;
  $initialVariant = $product->default_variant
    ?? $product->variants->where('is_active', true)->sortByDesc('is_default')->first()
    ?? $product->variants->first();
  $initialVariantStock = $initialVariant ? (int) $initialVariant->stock_quantity : 0;
  $initialVariantId = $initialVariant?->id;
  $initialVariantIsPurchasable = (bool) ($initialVariant?->is_active && $initialVariantStock > 0);
  $seoTitle = trim((string) ($product->meta_title ?: $product->name));
  $title = $seoTitle !== '' ? $seoTitle . ' | ' . $brandName : ($brandName . ' - ' . $product->name);
  $seoDescriptionSource = $product->meta_description ?: $product->short_description ?: $product->description ?: $product->notes;
  $metaDescription = \Illuminate\Support\Str::limit(trim(strip_tags((string) $seoDescriptionSource)), 160, '');
  $canonicalUrl = route('storefront.products.show', ['locale' => app()->getLocale(), 'product' => $product->slug]);
  $metaImage = $mainImageUrl ?: '';
  $metaImageAlt = $mainImage?->alt_text ?: $product->name;
  $ogType = 'product';
  $structuredData = array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product->name,
    'description' => $metaDescription,
    'image' => $mainImageUrl ? [$mainImageUrl] : null,
    'url' => $canonicalUrl,
    'sku' => $initialVariant?->sku,
    'brand' => [
      '@type' => 'Brand',
      'name' => $brandName,
    ],
    'category' => $product->categories->pluck('name')->filter()->implode(', '),
    'offers' => $product->display_price !== null ? [
      '@type' => 'Offer',
      'priceCurrency' => 'BHD',
      'price' => number_format((float) $product->display_price, 2, '.', ''),
      'availability' => $initialVariantIsPurchasable
        ? 'https://schema.org/InStock'
        : 'https://schema.org/OutOfStock',
      'url' => $canonicalUrl,
      'itemCondition' => 'https://schema.org/NewCondition',
    ] : null,
  ]);
  $customerReminderEmail = auth('customer')->user()?->email;
@endphp

@extends('frontend.layouts.app')

@section('content')
<div style="padding-top:72px;background:var(--gray-dark);border-bottom:1px solid var(--line-soft)">
  <div class="max-w-7xl mx-auto px-6 py-4">
    <div class="flex items-center gap-2 text-sm" style="color:var(--gray-light)">
      <a href="{{ route('storefront.home') }}">{{ __('storefront.common.home') }}</a>
      <span>›</span>
      @foreach ($product->categories as $category)
        <a href="{{ route('storefront.categories.show', ['category' => $category->slug]) }}">{{ $category->name }}</a>
        <span>›</span>
        @break
      @endforeach
      <span style="color:var(--white)">{{ $product->name }}</span>
    </div>
  </div>
</div>

<main class="max-w-7xl mx-auto px-6 py-12">
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
    <div id="galleryCol">
      <div class="main-img-wrap reveal" style="height:520px;margin-bottom:12px" onclick="openZoom(currentImg)">
        @if ($mainImage)
          <img src="{{ asset('storage/' . $mainImage->path) }}" alt="{{ $product->name }}" id="mainImg">
        @else
          <div id="mainImg" class="w-full h-full flex items-center justify-center text-neutral-500">{{ __('storefront.common.no_image') }}</div>
        @endif
      </div>
      <div class="grid grid-cols-5 gap-2 reveal">
        @foreach ($product->images as $image)
          <div class="thumb {{ $loop->first ? 'active' : '' }}" onclick="switchImg(this,'{{ asset('storage/' . $image->path) }}')">
            <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $image->alt_text ?: $product->name }}">
          </div>
        @endforeach
      </div>
    </div>

    <div id="infoCol">
      <div class="reveal">
        <p class="text-xs font-black mb-3" style="color:var(--gray-light);letter-spacing:0.25em">{{ $product->display_label }}</p>
        <h1 class="text-4xl md:text-5xl font-black mb-4" style="letter-spacing:-0.03em">{{ $product->name }}</h1>
        <p class="leading-8 text-base mb-6" style="color:var(--gray-light)">{{ $product->short_description }}</p>
        @if ($product->notes)
          <div class="mb-6 border px-4 py-3" style="border-color:var(--line-mid);background:rgb(var(--white-rgb) / .04);color:var(--gray-light)">
            <div class="flex items-start gap-2 text-xs">
              <i class='bx bx-error-circle' style="font-size:16px;line-height:1.4;flex:none"></i>
              <p class="leading-6">{{ $product->notes }}</p>
            </div>
          </div>
        @endif
        <div class="mb-8">
          <x-frontend.price
            :amount="$product->display_price"
            :compare-amount="$product->display_compare_price"
            wrapper-class="gap-2"
            amount-class="font-black text-[30px]"
            compare-class="text-[16px] line-through"
            secondary-class="text-sm"
            note-class="text-xs"
            unavailable-class="text-[30px]"
            row-class="flex items-center gap-3 flex-wrap"
          />
        </div>
      </div>

      <div class="reveal mb-8">
        <div class="flex items-center justify-between mb-4">
          <p class="text-sm font-bold">{{ __('storefront.common.size') }}</p>
          <button type="button" class="text-sm" style="color:var(--gray-light)" onclick="openSizeGuide()">{{ __('storefront.common.size_guide') }}</button>
        </div>
        <div class="grid grid-cols-4 gap-2">
          @foreach ($product->variants as $variant)
            <button
              class="size-btn {{ $initialVariantId === $variant->id ? 'active' : '' }} {{ $variant->is_active ? '' : 'unavailable' }}"
              type="button"
              data-variant-id="{{ $variant->id }}"
              data-stock-quantity="{{ (int) $variant->stock_quantity }}"
              data-is-active="{{ $variant->is_active ? '1' : '0' }}"
              data-size-label="{{ $variant->display_name }}"
              onclick="selectSize(this,'{{ $variant->display_name }}')"
            >
              {{ $variant->display_name }}
            </button>
          @endforeach
        </div>
      </div>

      <div class="reveal mb-8">
        <p class="text-sm font-bold mb-4">{{ __('storefront.common.quantity') }}</p>
        <div class="flex items-center gap-3">
          <button class="btn-icon" id="qtyDecreaseButton" type="button" onclick="changeQty(-1)">-</button>
          <div class="btn-icon" id="qtyDisplay" style="color:var(--white)">1</div>
          <button class="btn-icon" id="qtyIncreaseButton" type="button" onclick="changeQty(1)" @disabled(! $initialVariantIsPurchasable)>+</button>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 reveal">
        <button class="btn-primary w-full" id="productAddToCartButton" type="button" onclick="addToCart()" @disabled(! $initialVariantIsPurchasable)><span>{{ __('storefront.common.add_to_cart') }}</span></button>
        <button class="btn-outline w-full" type="button" onclick="shareProduct()"><span>{{ __('storefront.common.share_product') }}</span></button>
      </div>

      <div class="mt-4 reveal {{ $initialVariantStock < 10 ? '' : 'hidden' }}" id="stockWarning" style="border:1px solid var(--line-mid);background:rgb(var(--white-rgb) / .04);color:var(--gray-light);padding:12px 16px">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-start gap-2 text-xs">
            <i class='bx bx-error-circle' style="font-size:16px;line-height:1.4;flex:none"></i>
            <p class="leading-6" id="stockWarningText">
              {{ $initialVariantStock > 0
                  ? __('storefront.product.remaining_stock', ['count' => $initialVariantStock])
                  : __('storefront.product.out_of_stock') }}
            </p>
          </div>
          <div class="{{ $initialVariantStock === 0 ? '' : 'hidden' }}" id="reminderActions">
            <button class="btn-outline w-full sm:w-auto" type="button" onclick="handleReminderClick()">
              <span>{{ __('storefront.product.remind_me') }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <section class="mt-20">
    <div class="flex gap-8 mb-8 overflow-x-auto" style="border-bottom:1px solid var(--line-soft)">
      <button class="tab-btn active" type="button" onclick="switchProductTab(this,'description')">{{ __('storefront.common.description') }}</button>
    </div>
    <div class="tab-content active" id="tab-description">
      <div class="leading-8 text-base" style="color:var(--gray-light)">{{ $product->description ?: __('storefront.product.no_description') }}</div>
    </div>
  </section>

  @if($relatedProducts->count() > 0)
  <section class="mt-20">
    <div class="divider reveal"></div>
    <h2 class="text-3xl font-black mb-8 reveal" style="letter-spacing:-0.02em">{{ __('storefront.common.related_products') }}</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      @foreach ($relatedProducts as $relatedProduct)
        <x-frontend.product-card :product="$relatedProduct" />
      @endforeach
    </div>
  </section>
  @endif
</main>

<div class="sticky-bar" id="stickyBar">
  <div style="flex:1">
    <p style="font-size:12px;color:var(--gray-light)">{{ $product->name }}</p>
    <x-frontend.price
      :amount="$product->display_price"
      wrapper-class="gap-1"
      amount-class="font-black text-[18px]"
      secondary-class="text-[12px]"
      note-class="text-[10px]"
      unavailable-class="text-[18px]"
    />
  </div>
  <button class="btn-primary" id="stickyAddToCartButton" style="width:auto;padding:12px 24px;font-size:14px" type="button" onclick="addToCart()" @disabled(! $initialVariantIsPurchasable)><span>{{ __('storefront.common.add_to_cart') }}</span></button>
</div>

<div class="zoom-overlay" id="zoomOverlay" onclick="closeZoom()">
  <img id="zoomImg" src="" alt="">
  <button style="position:absolute;top:20px;inset-inline-start:20px;color:var(--white);background:none;border:none;font-size:24px" type="button" onclick="closeZoom()">✕</button>
</div>

<div class="toast" id="toast"></div>

<div class="modal-overlay" id="sizeGuideModal">
  <div class="modal-box" style="max-width:600px">
    <div style="padding:40px">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-black">{{ __('storefront.common.size_guide') }}</h2>
        <button onclick="closeSizeGuide()" style="color:var(--gray-light)" type="button">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <p class="text-sm mt-4" style="color:var(--gray-light)">{{ __('storefront.product.size_guide_copy') }}</p>
    </div>
  </div>
</div>

<div class="modal-overlay" id="reminderModal">
  <div class="modal-box" style="max-width:520px">
    <div style="padding:40px">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-xl font-black">{{ __('storefront.product.reminder_heading') }}</h2>
          <p class="text-sm mt-2" style="color:var(--gray-light)">{{ __('storefront.product.reminder_copy') }}</p>
        </div>
        <button onclick="closeReminderModal()" style="color:var(--gray-light)" type="button">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <form id="reminderForm" class="flex flex-col gap-4" onsubmit="submitReminder(event)">
        <input type="hidden" id="reminderVariantId" name="product_variant_id" value="{{ $initialVariantId }}">
        <div>
          <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.auth.email') }}</label>
          <input
            type="email"
            class="input-field"
            id="reminderEmail"
            name="email"
            value="{{ $customerReminderEmail }}"
            placeholder="{{ __('storefront.product.reminder_email_placeholder') }}"
            @if($customerReminderEmail) readonly @endif
          >
        </div>
        <button class="btn-primary w-full" type="submit">
          <span>{{ __('storefront.product.reminder_submit') }}</span>
        </button>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const productTranslations = {
  remainingStock: @json(__('storefront.product.remaining_stock', ['count' => '__COUNT__'])),
  outOfStock: @json(__('storefront.product.out_of_stock')),
  addedToCart: @json(__('storefront.product.added_to_cart')),
  unavailableVariant: @json(__('storefront.cart_variant_unavailable')),
  linkCopied: @json(__('storefront.product.link_copied')),
  reminderCreated: @json(__('storefront.product.reminder_created')),
  reminderFailed: @json(__('storefront.product.reminder_failed')),
};

const productId = @json($product->id);
let currentImg = document.getElementById('mainImg')?.getAttribute('src') || '';
let qty = 1;
let selectedVariantId = @json($initialVariantId);
const isCustomerAuthenticated = @json((bool) $customerReminderEmail);

function switchImg(thumb, src) {
  document.querySelectorAll('.thumb').forEach((item) => item.classList.remove('active'));
  thumb.classList.add('active');
  const mainImg = document.getElementById('mainImg');
  if (!mainImg || mainImg.tagName !== 'IMG') return;
  if (window.gsap) {
    gsap.to(mainImg, {
      opacity: 0,
      duration: 0.2,
      onComplete: () => {
        mainImg.src = src;
        currentImg = src;
        gsap.to(mainImg, { opacity: 1, duration: 0.3 });
      },
    });
  } else {
    mainImg.src = src;
    currentImg = src;
  }
}

function openZoom(src) {
  document.getElementById('zoomImg').src = src;
  document.getElementById('zoomOverlay').classList.add('open');
}

function closeZoom() {
  document.getElementById('zoomOverlay').classList.remove('open');
}

function selectSize(button) {
  document.querySelectorAll('.size-btn').forEach((item) => item.classList.remove('active'));
  button.classList.add('active');
  selectedVariantId = button.dataset.variantId || null;
  updateStockWarning(button);
  updateReminderState(button);
  syncQuantityAndActions(button);
}

function updateStockWarning(button) {
  const stockWarning = document.getElementById('stockWarning');
  const stockWarningText = document.getElementById('stockWarningText');
  if (!stockWarning || !stockWarningText || !button) return;
  const stockQuantity = Number(button.dataset.stockQuantity || 0);
  const isActive = button.dataset.isActive === '1';
  if (!isActive) {
    stockWarning.classList.remove('hidden');
    stockWarningText.textContent = productTranslations.unavailableVariant;
    return;
  }
  if (stockQuantity >= 10) {
    stockWarning.classList.add('hidden');
    return;
  }
  stockWarning.classList.remove('hidden');
  stockWarningText.textContent = stockQuantity > 0
    ? productTranslations.remainingStock.replace('__COUNT__', stockQuantity)
    : productTranslations.outOfStock;
}

function updateReminderState(button) {
  const reminderActions = document.getElementById('reminderActions');
  const reminderVariantId = document.getElementById('reminderVariantId');
  if (!reminderActions || !button) return;

  const stockQuantity = Number(button.dataset.stockQuantity || 0);
  const isActive = button.dataset.isActive === '1';

  if (reminderVariantId) {
    reminderVariantId.value = button.dataset.variantId || '';
  }

  reminderActions.classList.toggle('hidden', !isActive || stockQuantity !== 0);
}

function updateQtyDisplay() {
  document.getElementById('qtyDisplay').textContent = qty;
}

function syncQuantityAndActions(button = document.querySelector('.size-btn.active')) {
  const activeButton = button || document.querySelector('.size-btn.active');
  const qtyDecreaseButton = document.getElementById('qtyDecreaseButton');
  const qtyIncreaseButton = document.getElementById('qtyIncreaseButton');
  const addToCartButtons = [
    document.getElementById('productAddToCartButton'),
    document.getElementById('stickyAddToCartButton'),
  ].filter(Boolean);

  if (!activeButton) {
    return;
  }

  const stockQuantity = Number(activeButton.dataset.stockQuantity || 0);
  const isActive = activeButton.dataset.isActive === '1';
  const isPurchasable = isActive && stockQuantity > 0;

  if (!isPurchasable) {
    qty = 1;
  } else if (qty > stockQuantity) {
    qty = stockQuantity;
  } else if (qty < 1) {
    qty = 1;
  }

  updateQtyDisplay();

  if (qtyDecreaseButton) {
    qtyDecreaseButton.disabled = !isPurchasable || qty <= 1;
  }

  if (qtyIncreaseButton) {
    qtyIncreaseButton.disabled = !isPurchasable || qty >= stockQuantity;
  }

  addToCartButtons.forEach((buttonElement) => {
    buttonElement.disabled = !isPurchasable;
  });
}

function changeQty(diff) {
  const activeButton = document.querySelector('.size-btn.active');

  if (!activeButton) {
    return;
  }

  const stockQuantity = Number(activeButton.dataset.stockQuantity || 0);
  const isActive = activeButton.dataset.isActive === '1';

  if (!isActive || stockQuantity <= 0) {
    qty = 1;
    syncQuantityAndActions(activeButton);
    return;
  }

  qty = Math.min(stockQuantity, Math.max(1, qty + diff));
  syncQuantityAndActions(activeButton);
}

function showToast(message) {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.classList.add('show');
  window.setTimeout(() => toast.classList.remove('show'), 2500);
}

async function addToCart() {
  const activeButton = document.querySelector('.size-btn.active');

  if (!activeButton || !selectedVariantId) {
    return;
  }

  if (activeButton.dataset.isActive !== '1' || Number(activeButton.dataset.stockQuantity || 0) <= 0) {
    showToast(productTranslations.outOfStock);
    syncQuantityAndActions(activeButton);
    return;
  }

  try {
    const response = await fetch(@json(route('storefront.cart.items.store', ['locale' => app()->getLocale()])), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': @json(csrf_token()),
      },
      body: JSON.stringify({
        product_id: productId,
        product_variant_id: selectedVariantId,
        quantity: qty,
      }),
    });

    const data = await response.json();

    if (!response.ok) {
      const errorMessage = data.message || Object.values(data.errors || {}).flat()[0] || productTranslations.outOfStock;
      showToast(errorMessage);
      return;
    }

    if (typeof refreshNavbarCartSummary === 'function') {
      refreshNavbarCartSummary();
    } else {
      const cartCount = document.getElementById('cartCount');

      if (cartCount && data.cart?.items_count !== undefined) {
        cartCount.textContent = String(data.cart.items_count);
      }
    }

    showToast(data.message || productTranslations.addedToCart);
  } catch (error) {
    showToast(productTranslations.outOfStock);
  }
}

function shareProduct() {
  if (navigator.share) {
    navigator.share({ title: '{{ $product->name }}', url: window.location.href });
    return;
  }
  if (navigator.clipboard) {
    navigator.clipboard.writeText(window.location.href);
  }
  showToast(productTranslations.linkCopied);
}

function switchProductTab(button, tab) {
  document.querySelectorAll('.tab-btn').forEach((item) => item.classList.remove('active'));
  document.querySelectorAll('#tab-description').forEach((item) => item.classList.remove('active'));
  button.classList.add('active');
  document.getElementById(`tab-${tab}`).classList.add('active');
}

function openSizeGuide() {
  document.getElementById('sizeGuideModal').classList.add('open');
}

function closeSizeGuide() {
  document.getElementById('sizeGuideModal').classList.remove('open');
}

function openReminderModal() {
  document.getElementById('reminderModal')?.classList.add('open');
}

function closeReminderModal() {
  document.getElementById('reminderModal')?.classList.remove('open');
}

function handleReminderClick() {
  if (!selectedVariantId) return;

  if (isCustomerAuthenticated) {
    submitReminderRequest({ product_variant_id: selectedVariantId });
    return;
  }

  openReminderModal();
}

function submitReminder(event) {
  event.preventDefault();
  const form = document.getElementById('reminderForm');
  const formData = new FormData(form);

  submitReminderRequest({
    product_variant_id: formData.get('product_variant_id'),
    email: formData.get('email'),
  });
}

async function submitReminderRequest(payload) {
  try {
    const response = await fetch(@json(route('storefront.products.reminders.store', ['locale' => app()->getLocale(), 'product' => $product->slug])), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': @json(csrf_token()),
      },
      body: JSON.stringify(payload),
    });

    const data = await response.json();

    if (!response.ok) {
      const errorMessage = data.message || Object.values(data.errors || {}).flat()[0] || productTranslations.reminderFailed;
      showToast(errorMessage);
      return;
    }

    closeReminderModal();
    showToast(data.message || productTranslations.reminderCreated);
  } catch (error) {
    showToast(productTranslations.reminderFailed);
  }
}

document.getElementById('sizeGuideModal')?.addEventListener('click', function (event) {
  if (event.target === this) {
    closeSizeGuide();
  }
});

document.getElementById('reminderModal')?.addEventListener('click', function (event) {
  if (event.target === this) {
    closeReminderModal();
  }
});

window.addEventListener('scroll', () => {
  const bar = document.getElementById('stickyBar');
  if (window.innerWidth < 768 && bar) {
    bar.style.display = window.scrollY > 400 ? 'flex' : 'none';
  }
});

const activeVariantButton = document.querySelector('.size-btn.active');
updateStockWarning(activeVariantButton);
updateReminderState(activeVariantButton);
syncQuantityAndActions(activeVariantButton);
</script>
@endpush

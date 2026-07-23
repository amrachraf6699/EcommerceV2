@php
  $brandName = $frontendBrand['name'] ?? config('app.name');
  $mainImage = $product->images->sortByDesc('is_primary')->sortBy('sort_order')->first();
  $mainImageUrl = $mainImage ? asset('storage/' . $mainImage->path) : $product->primary_image_url;
  $galleryImages = $product->images->sortByDesc('is_primary')->sortBy('sort_order')->values();
  $initialVariant = $product->default_variant
    ?? $product->variants->where('is_active', true)->sortByDesc('is_default')->first()
    ?? $product->variants->first();
  $initialVariantStock = $initialVariant ? (int) $initialVariant->stock_quantity : 0;
  $initialVariantId = $initialVariant?->id;
  $initialVariantGroundType = trim((string) ($initialVariant?->ground_type?->label() ?? ''));
  $initialVariantIsPurchasable = (bool) ($initialVariant?->is_active && $initialVariantStock > 0);
  $isSoldOut = (bool) ($product->display_is_sold_out ?? false);
  $seoTitle = trim((string) ($product->meta_title ?: $product->name));
  $title = $seoTitle !== '' ? $seoTitle . ' | ' . $brandName : ($brandName . ' - ' . $product->name);
  $seoDescriptionSource = $product->meta_description ?: $product->short_description ?: $product->description ?: $product->notes;
  $metaDescription = \Illuminate\Support\Str::limit(trim(strip_tags((string) $seoDescriptionSource)), 160, '');
  $canonicalUrl = route('storefront.products.show', ['locale' => app()->getLocale(), 'product' => $product->slug]);
  $metaImage = $mainImageUrl ?: '';
  $metaImageAlt = $mainImage?->alt_text ?: $product->name;
  $sizeGuideCategory = $product->categories->first();
  $sizeGuideImageUrl = $sizeGuideCategory?->size_guide ? asset('storage/' . $sizeGuideCategory->size_guide) : null;
  $ogType = 'product';
  $structuredData = array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product->name,
    'description' => $metaDescription,
    'image' => $mainImageUrl ? [$mainImageUrl] : null,
    'url' => $canonicalUrl,
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
      <div class="main-img-wrap product-gallery-main reveal" style="height:520px;margin-bottom:12px" data-product-gallery-main onclick="openZoom(currentImg)">
        @if ($mainImage)
          <img src="{{ asset('storage/' . $mainImage->path) }}" alt="{{ $mainImage->alt_text ?: $product->name }}" id="mainImg">
        @else
          <div id="mainImg" class="w-full h-full flex items-center justify-center text-neutral-500">{{ __('storefront.common.no_image') }}</div>
        @endif

        @if ($product->label || $isSoldOut)
          <div class="badge-stack">
            @if ($product->label)
              <span class="badge">{{ $product->label }}</span>
            @endif
            @if ($isSoldOut)
              <span class="badge badge--sold-out">{{ __('storefront.badges.sold_out') }}</span>
            @endif
          </div>
        @endif

        @if ($galleryImages->count() > 1)
          <button class="product-gallery-nav product-gallery-nav--prev" type="button" aria-label="Previous image" onclick="event.stopPropagation(); showGalleryImage(currentImageIndex - 1)">
            <i class='bx bx-chevron-left'></i>
          </button>
          <button class="product-gallery-nav product-gallery-nav--next" type="button" aria-label="Next image" onclick="event.stopPropagation(); showGalleryImage(currentImageIndex + 1)">
            <i class='bx bx-chevron-right'></i>
          </button>
        @endif
      </div>
      <div class="product-gallery-thumbs reveal" aria-label="Product images">
        @foreach ($galleryImages as $image)
          <button class="thumb {{ $loop->first ? 'active' : '' }}" type="button" data-gallery-index="{{ $loop->index }}" onclick="showGalleryImage({{ $loop->index }})">
            <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $image->alt_text ?: $product->name }}">
          </button>
        @endforeach
      </div>
    </div>

    <div id="infoCol">
      <div class="reveal">
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
          <p class="product-ground-type product-ground-type--detail {{ $initialVariantGroundType !== '' ? '' : 'hidden' }}" id="selectedGroundType">
            <span>{{ __('storefront.common.ground_type') }}</span>
            <strong>{{ $initialVariantGroundType }}</strong>
          </p>
        </div>
      </div>

      <div class="reveal mb-8">
        <div class="flex items-center justify-between mb-4">
          <p class="text-sm font-bold">{{ app()->getLocale() === 'ar' ? 'الخيار' : 'Variant' }}</p>
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
              data-ground-type="{{ $variant->ground_type?->label() }}"
              onclick="selectSize(this)"
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
      <button class="tab-btn active p-4" type="button" onclick="switchProductTab(this,'description')">{{ __('storefront.common.description') }}</button>
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

<div class="zoom-overlay product-zoom-overlay" id="zoomOverlay" onclick="closeZoom()">
  <img id="zoomImg" src="" alt="">
  <button class="product-zoom-close" type="button" onclick="event.stopPropagation(); closeZoom()">&times;</button>
</div>

<div class="toast" id="toast"></div>

<div class="modal-overlay" id="sizeGuideModal">
  <div class="modal-box" style="max-width:600px;max-height:90vh;overflow-y:auto">
    <div style="padding:40px">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-black">{{ __('storefront.common.size_guide') }}</h2>
        <button onclick="closeSizeGuide()" style="color:var(--gray-light)" type="button">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <p class="text-sm mt-4" style="color:var(--gray-light)">{{ __('storefront.product.size_guide_copy') }}</p>
      @if ($sizeGuideImageUrl)
        <div class="mt-6 border p-4" style="border-color:var(--line-soft);background:rgb(var(--white-rgb) / .03)">
          <img
            src="{{ $sizeGuideImageUrl }}"
            alt="{{ __('storefront.common.size_guide') }} - {{ $sizeGuideCategory?->name }}"
            class="w-full h-auto"
            style="display:block;object-fit:contain"
          >
        </div>
      @endif
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

@push('styles')
<style>
  .product-gallery-main {
    touch-action: pan-y pinch-zoom;
    cursor: grab;
    background: var(--gray-dark);
    user-select: none;
  }

  .product-gallery-main.is-dragging {
    cursor: grabbing;
  }

  .product-gallery-main img {
    object-fit: contain;
    object-position: center;
    pointer-events: none;
    -webkit-user-drag: none;
  }

  .product-zoom-overlay {
    touch-action: pan-y;
    user-select: none;
  }

  .product-zoom-overlay img {
    max-width: min(92vw, 1100px);
    max-height: 88vh;
    object-fit: contain;
    pointer-events: none;
    -webkit-user-drag: none;
    transition: opacity .18s ease, transform .18s ease;
  }

  .product-zoom-overlay.is-swiping img {
    transition: none;
  }

  .product-zoom-close {
    position: absolute;
    top: 20px;
    inset-inline-start: 20px;
    color: var(--white);
    background: rgb(var(--black-rgb) / .45);
    border: 1px solid var(--line-mid);
    width: 42px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    line-height: 1;
    z-index: 2;
  }

  .product-gallery-thumbs {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    overflow-y: hidden;
    padding-bottom: 10px;
    scroll-snap-type: x mandatory;
    scrollbar-width: thin;
    scrollbar-color: var(--white) transparent;
  }

  .product-gallery-thumbs .thumb {
    flex: 0 0 clamp(72px, 18vw, 104px);
    border: 1px solid var(--line-soft);
    background: var(--gray-dark);
    cursor: pointer;
    scroll-snap-align: start;
  }

  .product-gallery-thumbs .thumb img {
    object-fit: contain;
    object-position: center;
  }

  .product-gallery-nav {
    position: absolute;
    top: 50%;
    z-index: 4;
    width: 44px;
    height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--line-mid);
    background: rgb(var(--black-rgb) / .72);
    color: var(--white);
    transform: translateY(-50%);
    transition: background-color .2s ease, border-color .2s ease, color .2s ease;
  }

  .product-gallery-nav:hover,
  .product-gallery-nav:focus-visible {
    border-color: var(--line-strong);
    background: var(--white);
    color: var(--black);
    outline: none;
  }

  .product-gallery-nav i {
    font-size: 28px;
    line-height: 1;
  }

  html[dir="rtl"] .product-gallery-nav i {
    transform: rotate(180deg);
  }

  .product-gallery-nav--prev {
    inset-inline-start: 12px;
  }

  .product-gallery-nav--next {
    inset-inline-end: 12px;
  }

  @media (max-width: 767px) {
    .product-gallery-main {
      height: 380px !important;
    }

    .product-gallery-nav {
      display: none;
    }
  }

  @media (hover: none) and (pointer: coarse) {
    .product-gallery-nav {
      display: none;
    }
  }
</style>
@endpush

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
const galleryImages = @json($galleryImages->map(fn ($image) => [
  'src' => asset('storage/' . $image->path),
  'alt' => $image->alt_text ?: $product->name,
])->values());
let currentImg = document.getElementById('mainImg')?.getAttribute('src') || '';
let currentImageIndex = galleryImages.findIndex((image) => image.src === currentImg);
if (currentImageIndex < 0) currentImageIndex = 0;
let qty = 1;
let selectedVariantId = @json($initialVariantId);
const isCustomerAuthenticated = @json((bool) $customerReminderEmail);

function syncGalleryThumb(index) {
  document.querySelectorAll('[data-gallery-index]').forEach((item) => {
    item.classList.toggle('active', Number(item.dataset.galleryIndex) === index);
  });

  const activeThumb = document.querySelector(`[data-gallery-index="${index}"]`);
  const thumbsRow = activeThumb?.closest('.product-gallery-thumbs');

  if (!activeThumb || !thumbsRow) {
    return;
  }

  const thumbRect = activeThumb.getBoundingClientRect();
  const rowRect = thumbsRow.getBoundingClientRect();
  const padding = 8;

  if (thumbRect.left < rowRect.left + padding) {
    thumbsRow.scrollBy({ left: thumbRect.left - rowRect.left - padding, behavior: 'smooth' });
  } else if (thumbRect.right > rowRect.right - padding) {
    thumbsRow.scrollBy({ left: thumbRect.right - rowRect.right + padding, behavior: 'smooth' });
  }
}

function setMainImage(src, alt = '') {
  const mainImg = document.getElementById('mainImg');
  if (!mainImg || mainImg.tagName !== 'IMG') return;
  if (window.gsap) {
    gsap.to(mainImg, {
      opacity: 0,
      duration: 0.2,
      onComplete: () => {
        mainImg.src = src;
        mainImg.alt = alt;
        currentImg = src;
        gsap.to(mainImg, { opacity: 1, duration: 0.3 });
      },
    });
  } else {
    mainImg.src = src;
    mainImg.alt = alt;
    currentImg = src;
  }
}

function showGalleryImage(index) {
  if (!galleryImages.length) return;

  currentImageIndex = (index + galleryImages.length) % galleryImages.length;
  const image = galleryImages[currentImageIndex];

  syncGalleryThumb(currentImageIndex);
  setMainImage(image.src, image.alt);
  syncZoomImage();
}

function switchImg(thumb, src) {
  const index = Number(thumb?.dataset?.galleryIndex ?? galleryImages.findIndex((image) => image.src === src));
  showGalleryImage(Number.isNaN(index) ? 0 : index);
}

function openZoom(src) {
  if (!src) return;
  const imageIndex = galleryImages.findIndex((image) => image.src === src);

  if (imageIndex >= 0) {
    currentImageIndex = imageIndex;
  }

  syncZoomImage();
  document.getElementById('zoomOverlay')?.classList.add('open');
}

function closeZoom() {
  document.getElementById('zoomOverlay')?.classList.remove('open');
}

function syncZoomImage() {
  const zoomImg = document.getElementById('zoomImg');
  const image = galleryImages[currentImageIndex];

  if (!zoomImg || !image) {
    return;
  }

  zoomImg.src = image.src;
  zoomImg.alt = image.alt || '';
}

function showZoomImage(index) {
  if (!galleryImages.length) return;

  showGalleryImage(index);
  syncZoomImage();
}

function selectSize(button) {
  document.querySelectorAll('.size-btn').forEach((item) => item.classList.remove('active'));
  button.classList.add('active');
  selectedVariantId = button.dataset.variantId || null;
  updateGroundType(button);
  updateStockWarning(button);
  updateReminderState(button);
  syncQuantityAndActions(button);
}

function updateGroundType(button) {
  const groundTypeNode = document.getElementById('selectedGroundType');
  const groundTypeValue = button?.dataset?.groundType || '';
  const groundTypeText = groundTypeNode?.querySelector('strong');

  if (!groundTypeNode || !groundTypeText) return;

  groundTypeText.textContent = groundTypeValue;
  groundTypeNode.classList.toggle('hidden', groundTypeValue === '');
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

const productGalleryMain = document.querySelector('[data-product-gallery-main]');
if (productGalleryMain && galleryImages.length > 1) {
  let galleryPointerStartX = 0;
  let galleryPointerStartY = 0;
  let galleryPointerCurrentX = 0;
  let galleryPointerId = null;
  let galleryPointerActive = false;
  let suppressNextGalleryClick = false;
  const swipeThreshold = 48;
  const verticalTolerance = 80;

  const beginGallerySwipe = (event) => {
    if (event.target.closest('.product-gallery-nav')) {
      return;
    }

    galleryPointerStartX = event.clientX;
    galleryPointerStartY = event.clientY;
    galleryPointerCurrentX = event.clientX;
    galleryPointerId = event.pointerId;
    galleryPointerActive = true;
    productGalleryMain.classList.add('is-dragging');
    productGalleryMain.setPointerCapture?.(event.pointerId);
  };

  const trackGallerySwipe = (event) => {
    if (!galleryPointerActive) return;

    galleryPointerCurrentX = event.clientX;

    const deltaX = galleryPointerCurrentX - galleryPointerStartX;
    const deltaY = event.clientY - galleryPointerStartY;

    if (Math.abs(deltaX) > 10 && Math.abs(deltaX) > Math.abs(deltaY)) {
      event.preventDefault();
    }
  };

  const endGallerySwipe = (event) => {
    if (!galleryPointerActive) return false;

    trackGallerySwipe(event);

    const deltaX = galleryPointerCurrentX - galleryPointerStartX;
    const deltaY = event.clientY - galleryPointerStartY;
    galleryPointerActive = false;
    productGalleryMain.classList.remove('is-dragging');
    productGalleryMain.releasePointerCapture?.(galleryPointerId);
    galleryPointerId = null;

    if (Math.abs(deltaX) < swipeThreshold || Math.abs(deltaY) > verticalTolerance) {
      return false;
    }

    if (deltaX < 0) {
      showGalleryImage(currentImageIndex + 1);
    } else {
      showGalleryImage(currentImageIndex - 1);
    }

    suppressNextGalleryClick = true;
    return true;
  };

  productGalleryMain.addEventListener('pointerdown', (event) => {
    if (event.button !== undefined && event.button !== 0) return;
    beginGallerySwipe(event);
  });

  productGalleryMain.addEventListener('pointermove', (event) => {
    if (galleryPointerId !== null && event.pointerId !== galleryPointerId) return;
    trackGallerySwipe(event);
  });

  productGalleryMain.addEventListener('pointerup', (event) => {
    if (galleryPointerId !== null && event.pointerId !== galleryPointerId) return;
    if (endGallerySwipe(event)) {
      event.preventDefault();
      event.stopPropagation();
    }
  });

  productGalleryMain.addEventListener('pointercancel', () => {
    galleryPointerActive = false;
    galleryPointerId = null;
    productGalleryMain.classList.remove('is-dragging');
  });

  productGalleryMain.addEventListener('click', (event) => {
    if (suppressNextGalleryClick) {
      event.preventDefault();
      event.stopPropagation();
      suppressNextGalleryClick = false;
    }
  }, true);
}

const zoomOverlay = document.getElementById('zoomOverlay');
const zoomImg = document.getElementById('zoomImg');
if (zoomOverlay && zoomImg && galleryImages.length > 1) {
  let zoomPointerStartX = 0;
  let zoomPointerStartY = 0;
  let zoomPointerCurrentX = 0;
  let zoomPointerId = null;
  let zoomPointerActive = false;
  let suppressNextZoomClick = false;
  const zoomSwipeThreshold = 48;
  const zoomVerticalTolerance = 90;

  const beginZoomSwipe = (event) => {
    if (event.target.closest('.product-zoom-close')) {
      return;
    }

    zoomPointerStartX = event.clientX;
    zoomPointerStartY = event.clientY;
    zoomPointerCurrentX = event.clientX;
    zoomPointerId = event.pointerId;
    zoomPointerActive = true;
    zoomOverlay.classList.add('is-swiping');
    zoomOverlay.setPointerCapture?.(event.pointerId);
  };

  const trackZoomSwipe = (event) => {
    if (!zoomPointerActive) return;

    zoomPointerCurrentX = event.clientX;

    const deltaX = zoomPointerCurrentX - zoomPointerStartX;
    const deltaY = event.clientY - zoomPointerStartY;

    if (Math.abs(deltaX) > 10 && Math.abs(deltaX) > Math.abs(deltaY)) {
      event.preventDefault();
      zoomImg.style.transform = `translateX(${Math.max(-70, Math.min(70, deltaX * 0.35))}px)`;
    }
  };

  const endZoomSwipe = (event) => {
    if (!zoomPointerActive) return false;

    trackZoomSwipe(event);

    const deltaX = zoomPointerCurrentX - zoomPointerStartX;
    const deltaY = event.clientY - zoomPointerStartY;
    zoomPointerActive = false;
    zoomPointerId = null;
    zoomOverlay.classList.remove('is-swiping');
    zoomOverlay.releasePointerCapture?.(event.pointerId);
    zoomImg.style.transform = '';

    if (Math.abs(deltaX) < zoomSwipeThreshold || Math.abs(deltaY) > zoomVerticalTolerance) {
      return false;
    }

    showZoomImage(deltaX < 0 ? currentImageIndex + 1 : currentImageIndex - 1);
    suppressNextZoomClick = true;
    return true;
  };

  zoomOverlay.addEventListener('pointerdown', (event) => {
    if (!zoomOverlay.classList.contains('open')) return;
    if (event.button !== undefined && event.button !== 0) return;
    beginZoomSwipe(event);
  });

  zoomOverlay.addEventListener('pointermove', (event) => {
    if (zoomPointerId !== null && event.pointerId !== zoomPointerId) return;
    trackZoomSwipe(event);
  });

  zoomOverlay.addEventListener('pointerup', (event) => {
    if (zoomPointerId !== null && event.pointerId !== zoomPointerId) return;
    if (endZoomSwipe(event)) {
      event.preventDefault();
      event.stopPropagation();
    }
  });

  zoomOverlay.addEventListener('pointercancel', () => {
    zoomPointerActive = false;
    zoomPointerId = null;
    zoomOverlay.classList.remove('is-swiping');
    zoomImg.style.transform = '';
  });

  zoomOverlay.addEventListener('click', (event) => {
    if (suppressNextZoomClick) {
      event.preventDefault();
      event.stopImmediatePropagation();
      suppressNextZoomClick = false;
    }
  }, true);
}

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

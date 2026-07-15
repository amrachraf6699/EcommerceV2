@php
    $title = ($frontendBrand['name'] ?? config('app.name')) . ' - ' . __('storefront.common.home');
    $homeBrandsSectionBackgroundColor = setting('appearance.home_brands_section_background_color', '#000000');
    $homeShopBySizeSectionBackgroundColor = setting('appearance.home_shop_by_size_section_background_color', '#0a0a0a');
    $homeShopBySizeCardBackgroundImage = setting('appearance.home_shop_by_size_card_background_image');
    $homeShopBySizeCardBackgroundImageUrl = $homeShopBySizeCardBackgroundImage
        ? asset('storage/' . $homeShopBySizeCardBackgroundImage)
        : asset('shop-by-size-bg.jpg');
    $homeNewArrivalsSectionBackgroundColor = setting('appearance.home_new_arrivals_section_background_color', '#121212');
    $categoriesTitle = setting('appearance.categories_title_' . app()->getLocale(), __('storefront.home.athletic_brands')) ?: __('storefront.home.athletic_brands');
    $primaryCategory = $featuredCategories->first();
    $secondaryCategories = $featuredCategories->skip(1);
    $defaultHomeProductOption = $homeProductOptions->firstWhere('type', 'featured') ?? $homeProductOptions->first();
    $defaultHomeProductsUrl = route('storefront.home.products-feed', [
        'locale' => app()->getLocale(),
        'type' => $defaultHomeProductOption['type'] ?? 'featured',
        'category_id' => $defaultHomeProductOption['category_id'] ?? null,
    ]);
    $featuredCatalogUrl = route('storefront.catalog', ['sort' => 'featured']);
    $newCatalogUrl = route('storefront.catalog', ['sort' => 'newest']);
    $sliderHorizontalClasses = [
        'left' => 'ml-0 mr-auto items-start text-left',
        'center' => 'mx-auto items-center text-center',
        'right' => 'ml-auto mr-0 items-end text-right',
    ];
    $sliderActionsClasses = [
        'left' => 'justify-start',
        'center' => 'justify-center',
        'right' => 'justify-end',
    ];
    $sliderVerticalClasses = [
        'top' => 'justify-start pt-32 md:pt-40 pb-16',
        'center' => 'justify-center py-16',
        'bottom' => 'justify-end pb-24 md:pb-32 pt-16',
    ];
@endphp

@extends('frontend.layouts.app')

@section('content')
<section class="relative" style="padding-top:72px">
  <div class="relative overflow-hidden hero-slider" data-hero-slider style="min-height:100vh">
    @forelse ($heroSliders as $slider)
      @php
        $horizontalAlign = $slider->horizontal_align ?? 'center';
        $verticalAlign = $slider->vertical_align ?? 'center';
        $textColor = $slider->text_color ?: '#f5f5f0';
        $buttonBackgroundColor = $slider->button_background_color ?: '#111111';
        $buttonTextColor = $slider->button_text_color ?: '#ffffff';
        $overlayOpacityStart = is_numeric($slider->overlay_opacity_start ?? null) ? number_format((float) $slider->overlay_opacity_start, 2, '.', '') : '0.90';
        $overlayOpacityEnd = is_numeric($slider->overlay_opacity_end ?? null) ? number_format((float) $slider->overlay_opacity_end, 2, '.', '') : '0.55';
      @endphp
      <div class="hero-slide {{ $loop->first ? 'active' : '' }}" id="slide-{{ $loop->index }}">
        <div
          class="absolute inset-0 hero-slide__media {{ $slider->image ? 'hero-slide__media--image' : '' }}"
          @if ($slider->image)
            style="background-image:url('{{ asset('storage/' . $slider->image) }}')"
          @endif
        >
          <div class="absolute inset-0" style="background:linear-gradient(135deg,rgb(var(--overlay-rgb) / {{ $overlayOpacityStart }}),rgb(var(--overlay-rgb) / {{ $overlayOpacityEnd }}))"></div>
        </div>
        <div class="hero-slide__inner relative max-w-7xl mx-auto px-6 min-h-screen flex flex-col {{ $sliderVerticalClasses[$verticalAlign] ?? $sliderVerticalClasses['center'] }}">
          <div class="max-w-3xl w-full flex flex-col {{ $sliderHorizontalClasses[$horizontalAlign] ?? $sliderHorizontalClasses['center'] }}" style="color:{{ $textColor }}">
            @if ($slider->title)
              <h1 class="text-5xl md:text-7xl font-black leading-none mb-6 reveal" style="letter-spacing:-0.04em">{{ $slider->title }}</h1>
            @endif

            @if ($slider->subtitle)
              <p class="text-base md:text-lg max-w-2xl leading-8 mb-8 reveal" style="color:{{ $textColor }}">{{ $slider->subtitle }}</p>
            @endif

            <div class="flex flex-wrap gap-4 reveal hero-slide__actions {{ $sliderActionsClasses[$horizontalAlign] ?? $sliderActionsClasses['center'] }}">
              @if ($slider->link)
                <a href="{{ $slider->link }}" class="btn-primary hero-slide__cta" style="--hero-slide-button-bg:{{ $buttonBackgroundColor }};--hero-slide-button-text:{{ $buttonTextColor }};">
                  <span>{{ __('storefront.common.discover') }}</span>
                </a>
              @endif

              @if ($primaryCategory)
                <a href="{{ route('storefront.categories.index') }}" class="btn-outline hero-slide__secondary" style="--hero-slide-outline-color:{{ $textColor }};">
                  <span>{{ __('storefront.common.browse_categories') }}</span>
                </a>
              @endif
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="relative">
        <div class="absolute inset-0" style="background:linear-gradient(135deg,rgb(var(--overlay-rgb) / .95),rgb(var(--surface-alt-rgb) / .88))"></div>
        <div class="hero-slide__inner relative max-w-7xl mx-auto px-6 min-h-screen flex items-center justify-center text-center">
          <div class="max-w-3xl">
            <h1 class="text-5xl md:text-7xl font-black leading-none mb-6 reveal" style="letter-spacing:-0.04em">{{ $frontendBrand['name'] ?? config('app.name') }}</h1>
            <div class="flex flex-wrap gap-4 justify-center reveal">
              <a href="{{ route('storefront.catalog') }}" class="btn-primary"><span>{{ __('storefront.common.start_shopping') }}</span></a>
            </div>
          </div>
        </div>
      </div>
    @endforelse

    @if ($heroSliders->count() > 1)
      <div class="absolute bottom-10 right-6 flex gap-3 z-20">
        @foreach ($heroSliders as $slider)
          <button type="button" class="slider-dot {{ $loop->first ? 'active' : '' }}" onclick="goToSlide({{ $loop->index }})" style="width:14px;height:14px;border:1px solid var(--line-strong);background:{{ $loop->first ? 'var(--white)' : 'transparent' }}"></button>
        @endforeach
      </div>
    @endif
  </div>
</section>

<section class="py-16 px-6" style="background:{{ $homeBrandsSectionBackgroundColor }}">
  <div class="max-w-7xl mx-auto">
    <div class="flex items-end justify-center gap-6 mb-12 text-center">
      <div>
        <h2 class="text-3xl md:text-4xl font-black mb-3 reveal" style="letter-spacing:-0.02em">{{ $categoriesTitle }}</h2>
      </div>
    </div>
    @if ($primaryCategory)
      @include('frontend.partials.category-collection', ['categories' => collect([$primaryCategory])->merge($secondaryCategories), 'largeFirst' => true])
    @endif
  </div>
</section>

@if ($shopBySizeGroups->isNotEmpty())
<section class="py-20 px-6 home-shop-by-size-section" style="background:{{ $homeShopBySizeSectionBackgroundColor }};border-top:1px solid var(--line-soft)">
  <div class="max-w-7xl mx-auto">
    <div class="text-center mb-12 reveal">
      <h2 class="text-3xl font-black mb-3" style="letter-spacing:-0.02em">{{ __('storefront.home.shop_by_size') }}</h2>
    </div>

    <div class="collection-scroller shop-by-size-scroller">
      @foreach ($shopBySizeGroups as $group)
        <a href="{{ $group['url'] }}" class="shop-by-size-card reveal" aria-label="{{ __('storefront.home.shop_by_size') }} {{ $group['from'] === $group['to'] ? $group['from'] : $group['from'] . ' - ' . $group['to'] }}">
          <span class="shop-by-size-card__overlay"></span>
          <span class="shop-by-size-card__content">
            @if ($group['from'] === $group['to'])
              <strong>{{ $group['from'] }}</strong>
            @else
              <strong>{{ $group['from'] }}</strong>
              <span>{{ $group['to'] }}</span>
            @endif
          </span>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endif

<section class="py-16 px-6" style="background:var(--gray-dark);border-top:1px solid var(--line-soft)">
  <div class="max-w-7xl mx-auto">
    <div class="flex flex-col items-center justify-center gap-6 mb-12 text-center">
      <div class="w-full flex justify-center">
        <label for="home-products-trigger" class="sr-only">{{ __('storefront.home.featured_products') }}</label>
        <div
          class="home-products-filter reveal z-9999"
          data-home-products-filter
          data-endpoint="{{ route('storefront.home.products-feed', ['locale' => app()->getLocale()]) }}"
          data-loading-label="{{ __('storefront.category_show.loading_products') }}"
          data-selected-type="{{ $defaultHomeProductOption['type'] ?? 'featured' }}"
          data-selected-category-id="{{ $defaultHomeProductOption['category_id'] ?? '' }}"
          data-selected-view-all-url="{{ ($defaultHomeProductOption['type'] ?? 'featured') === 'featured' ? $featuredCatalogUrl : (($defaultHomeProductOption['type'] ?? 'featured') === 'new' ? $newCatalogUrl : route('storefront.categories.show', ['locale' => app()->getLocale(), 'category' => $defaultHomeProductOption['slug'] ?? null])) }}"
        >
          <button
            type="button"
            id="home-products-trigger"
            class="home-products-filter__trigger"
            data-home-products-trigger
            aria-haspopup="listbox"
            aria-expanded="false"
          >
            <span class="home-products-filter__title" data-home-products-label>{{ $defaultHomeProductOption['label'] ?? __('storefront.home.featured_products') }}</span>
          </button>
          <button
            type="button"
            class="home-products-filter__arrow"
            data-home-products-arrow
            aria-label="{{ __('storefront.common.apply') }}"
            aria-expanded="false"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="M6 15l6-6 6 6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
          <div class="home-products-filter__menu z-999" data-home-products-menu hidden>
            @foreach ($homeProductOptions as $option)
              @php
                $optionType = $option['type'] ?? 'featured';
                $optionCategoryId = $option['category_id'] ?? null;
                $optionUrl = $optionType === 'featured'
                    ? $featuredCatalogUrl
                    : ($optionType === 'new'
                        ? $newCatalogUrl
                        : route('storefront.categories.show', [
                            'locale' => app()->getLocale(),
                            'category' => $option['slug'] ?? null,
                        ]));
              @endphp
              <button
                type="button"
                class="home-products-filter__option {{ $loop->first ? 'is-active' : '' }}"
                data-home-products-option
                data-label="{{ $option['label'] }}"
                data-type="{{ $optionType }}"
                data-category-id="{{ $optionCategoryId }}"
                data-view-all-url="{{ $optionUrl }}"
                role="option"
                aria-selected="{{ $loop->first ? 'true' : 'false' }}"
              >
                <span>{{ $option['label'] }}</span>
              </button>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    <div
      data-home-products-wrapper
      data-default-url="{{ $defaultHomeProductsUrl }}"
      data-loading-label="{{ __('storefront.category_show.loading_products') }}"
    >
      @if ($featuredProducts->isNotEmpty())
        @include('frontend.partials.product-collection', ['products' => $featuredProducts])
      @else
        <div class="text-center py-16 border reveal" style="border-color:var(--line-soft)">
          <p class="text-xl font-black mb-3">{{ __('storefront.home.no_featured_title') }}</p>
        </div>
      @endif
    </div>
  </div>
</section>

<section class="py-20 px-6" style="background:{{ $homeNewArrivalsSectionBackgroundColor }}">
  <div class="max-w-7xl mx-auto">
    <div class="text-center mb-14 reveal">
      <h2 class="text-3xl font-black mb-3" style="letter-spacing:-0.02em">{{ __('storefront.home.new_arrivals') }}</h2>
      <p style="color:var(--gray-light)">{{ __('storefront.home.new_arrivals_copy') }}</p>
    </div>
    @include('frontend.partials.product-collection', ['products' => $newArrivalProducts])
  </div>
</section>

@if ($clients->isNotEmpty())
<section class="py-20 px-6 home-clients-section" style="background:var(--gray-dark);border-top:1px solid var(--line-soft)">
  <div class="max-w-7xl mx-auto">
    <div class="text-center mb-14 reveal">
      <h2 class="text-3xl font-black mb-3" style="letter-spacing:-0.02em">{{ __('storefront.home.clients') }}</h2>
      <p style="color:var(--gray-light)">{{ __('storefront.home.clients_copy') }}</p>
    </div>
    @include('frontend.partials.client-collection', ['clients' => $clients])
  </div>
</section>
@endif
@endsection

@push('styles')
<style>
  .hero-slider{touch-action:pan-y;}
  .hero-slide{position:absolute;inset:0;opacity:0;pointer-events:none;z-index:0;transition:opacity .8s ease;}
  .hero-slide.active{opacity:1;position:relative;pointer-events:auto;z-index:1;}
  .hero-slide__media{background:#000;}
  .hero-slide__media--image{background-position:center;background-repeat:no-repeat;background-size:cover;}
  .hero-slide__inner{min-height:100vh;}
  .hero-slide__cta{background:var(--hero-slide-button-bg);color:var(--hero-slide-button-text);border-color:var(--hero-slide-button-bg);}
  .hero-slide__cta::before{display:none;}
  .hero-slide__cta:hover{background:var(--hero-slide-button-bg);color:var(--hero-slide-button-text);transform:translateY(-2px);}
  .hero-slide__secondary{color:var(--hero-slide-outline-color);border-color:var(--hero-slide-outline-color);}
  .hero-slide__secondary::before{display:none;}
  .hero-slide__secondary:hover{background:transparent;color:var(--hero-slide-outline-color);border-color:var(--hero-slide-outline-color);transform:translateY(-2px);}
  .home-clients-section .client-card__media{aspect-ratio:4 / 5;}
  .home-clients-section .client-masonry__item--featured .client-card__media{min-height:420px;}
  .home-clients-section .client-masonry__item--tall .client-card__media{min-height:360px;}
  .shop-by-size-scroller{justify-content:flex-start;gap:32px;padding-bottom:20px;}
  .shop-by-size-card{
    position:relative;
    flex:0 0 clamp(156px,19vw,230px);
    aspect-ratio:1;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
    border-radius:50%;
    border:1px solid var(--line-soft);
    background-image:url('{{ $homeShopBySizeCardBackgroundImageUrl }}');
    background-position:center;
    background-size:cover;
    transition:transform .28s ease,border-color .28s ease,box-shadow .28s ease;
  }
  .shop-by-size-card:hover{
    transform:translateY(-6px);
    border-color:var(--line-strong);
  }
  .shop-by-size-card__overlay{
    position:absolute;
    inset:0;
    background:rgb(var(--overlay-rgb) / .38);
  }
  .shop-by-size-card__content{
    position:relative;
    z-index:1;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    color:#fff;
    text-align:center;
    text-shadow:0 3px 16px rgb(0 0 0 / .55);
    line-height:1;
  }
  .shop-by-size-card__content strong{
    font-size:clamp(2rem,3.6vw,3.25rem);
    font-weight:900;
  }
  .shop-by-size-card__content span{
    margin-top:10px;
    padding-top:10px;
    min-width:68px;
    border-top:2px solid rgb(255 255 255 / .72);
    font-size:clamp(1.25rem,2.2vw,1.9rem);
    font-weight:900;
  }
  .home-products-filter{
    position:relative;
    isolation:isolate;
    display:inline-flex;
    align-items:center;
    gap:10px;
    max-width:min(100%,520px);
    padding:14px 16px 14px 18px;
    border:1px solid var(--line-soft);
    background:linear-gradient(135deg,rgb(var(--surface-rgb) / .96),rgb(var(--surface-alt-rgb) / .82));
    box-shadow:var(--panel-shadow-soft);
    backdrop-filter:blur(10px);
    z-index:30;
  }
  .home-products-filter__trigger{
    flex:1 1 auto;
    min-width:0;
    border:0;
    background:transparent;
    color:var(--white);
    text-align:start;
    cursor:pointer;
  }
  .home-products-filter__title{
    display:block;
    font-size:1.875rem;
    font-weight:900;
    letter-spacing:-0.02em;
    line-height:1.1;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
  }
  .home-products-filter__arrow{
    flex:0 0 auto;
    width:42px;
    height:42px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border:1px solid var(--line-mid);
    background:rgb(var(--white-rgb) / .04);
    color:var(--white);
    cursor:pointer;
    transition:background .2s ease,border-color .2s ease,transform .2s ease;
  }
  .home-products-filter__arrow:hover,
  .home-products-filter__trigger:hover + .home-products-filter__arrow{
    background:rgb(var(--white-rgb) / .08);
    border-color:var(--line-strong);
  }
  .home-products-filter__arrow svg{width:18px;height:18px;transition:transform .2s ease;}
  .home-products-filter.is-open .home-products-filter__arrow svg{transform:rotate(180deg);}
  .home-products-filter__trigger:focus,
  .home-products-filter__arrow:focus{outline:none;}
  .home-products-filter:focus-within{border-color:var(--line-strong);}
  .home-products-filter__menu{
    position:absolute;
    top:calc(100% + 12px);
    inset-inline-start:0;
    min-width:100%;
    width:max-content;
    max-width:min(100vw - 48px, 440px);
    padding:10px;
    border:1px solid var(--line-soft);
    background:linear-gradient(180deg,rgb(var(--surface-rgb) / .98),rgb(var(--surface-alt-rgb) / .96));
    box-shadow:var(--panel-shadow);
    z-index:40;
  }
  .home-products-filter__option{
    width:100%;
    display:flex;
    align-items:center;
    justify-content:flex-start;
    padding:14px 16px;
    border:1px solid transparent;
    background:transparent;
    color:var(--white);
    font-size:1rem;
    font-weight:800;
    text-align:start;
    cursor:pointer;
    transition:background .2s ease,border-color .2s ease,transform .2s ease;
  }
  .home-products-filter__option:hover,
  .home-products-filter__option.is-active{
    background:rgb(var(--white-rgb) / .06);
    border-color:var(--line-soft);
    transform:translateY(-1px);
  }
  .home-products-loading{
    display:grid;place-items:center;min-height:320px;border:1px solid var(--line-soft);border-radius:28px;
    background:linear-gradient(180deg,rgb(var(--surface-rgb) / .88),rgb(var(--surface-alt-rgb) / .72));
  }
  .home-products-loading__pill{
    display:inline-flex;align-items:center;gap:12px;padding:14px 18px;border-radius:999px;
    border:1px solid var(--line-soft);background:rgb(var(--surface-alt-rgb) / .88);font-weight:800;
  }
  .home-products-loading__spinner{
    width:18px;height:18px;border-radius:999px;border:2px solid rgb(var(--text-rgb) / .18);
    border-top-color:var(--text);animation:home-products-spin .9s linear infinite;
  }
  @keyframes home-products-spin{to{transform:rotate(360deg);}}
  @media (max-width: 767px){
    .hero-slider{min-height:72vh !important;}
    .hero-slide__inner{min-height:72vh !important;}
    .hero-slide__inner.justify-start{padding-top:6.5rem !important;padding-bottom:2.5rem !important;}
    .hero-slide__inner.justify-center{padding-top:4.5rem !important;padding-bottom:2.5rem !important;}
    .hero-slide__inner.justify-end{padding-top:3rem !important;padding-bottom:4.5rem !important;}
    .home-clients-section .client-card__media{aspect-ratio:4 / 5.4;}
    .home-clients-section .client-masonry__item--featured .client-card__media,
    .home-clients-section .client-masonry__item--tall .client-card__media{min-height:auto;}
    .home-products-filter{
      width:100%;
      max-width:none;
      padding:12px;
      gap:8px;
    }
    .home-products-filter__title{font-size:1.45rem;}
    .home-products-filter__arrow{width:38px;height:38px;}
    .home-products-filter__menu{
      width:100%;
      max-width:none;
    }
    .home-products-filter__option{padding:13px 14px;font-size:.95rem;}
    .shop-by-size-scroller{gap:22px;}
    .shop-by-size-card{flex-basis:clamp(150px,48vw,190px);}
  }
</style>
@endpush

@push('scripts')
<script>
let currentSlide = 0;
let sliderIntervalId = null;
const slides = Array.from(document.querySelectorAll('.hero-slide'));
const dots = Array.from(document.querySelectorAll('.slider-dot'));
const heroSlider = document.querySelector('[data-hero-slider]');

function goToSlide(index) {
  if (!slides.length) return;
  slides[currentSlide]?.classList.remove('active');
  dots[currentSlide]?.classList.remove('active');
  if (dots[currentSlide]) dots[currentSlide].style.background = 'transparent';
  currentSlide = index;
  slides[currentSlide]?.classList.add('active');
  dots[currentSlide]?.classList.add('active');
  if (dots[currentSlide]) dots[currentSlide].style.background = 'var(--white)';
}

function goToNextSlide() {
  goToSlide((currentSlide + 1) % slides.length);
}

function goToPreviousSlide() {
  goToSlide((currentSlide - 1 + slides.length) % slides.length);
}

function startSliderAutoplay() {
  if (slides.length <= 1) return;

  window.clearInterval(sliderIntervalId);
  sliderIntervalId = window.setInterval(goToNextSlide, 5000);
}

if (slides.length > 1) {
  startSliderAutoplay();
}

if (heroSlider && slides.length > 1) {
  let pointerStartX = 0;
  let pointerStartY = 0;
  let pointerCurrentX = 0;
  let isPointerActive = false;
  const swipeThreshold = 50;
  const verticalTolerance = 80;

  const beginSwipe = (clientX, clientY) => {
    pointerStartX = clientX;
    pointerStartY = clientY;
    pointerCurrentX = clientX;
    isPointerActive = true;
  };

  const trackSwipe = (clientX) => {
    if (!isPointerActive) return;
    pointerCurrentX = clientX;
  };

  const endSwipe = (clientX, clientY) => {
    if (!isPointerActive) return;

    trackSwipe(clientX);

    const deltaX = pointerCurrentX - pointerStartX;
    const deltaY = clientY - pointerStartY;
    isPointerActive = false;

    if (Math.abs(deltaX) < swipeThreshold || Math.abs(deltaY) > verticalTolerance) {
      return;
    }

    if (deltaX < 0) {
      goToNextSlide();
    } else {
      goToPreviousSlide();
    }

    startSliderAutoplay();
  };

  heroSlider.addEventListener('touchstart', (event) => {
    const touch = event.touches[0];

    if (!touch) return;

    beginSwipe(touch.clientX, touch.clientY);
  }, { passive: true });

  heroSlider.addEventListener('touchmove', (event) => {
    const touch = event.touches[0];

    if (!touch) return;

    trackSwipe(touch.clientX);
  }, { passive: true });

  heroSlider.addEventListener('touchend', (event) => {
    const touch = event.changedTouches[0];

    if (!touch) return;

    endSwipe(touch.clientX, touch.clientY);
  });

  heroSlider.addEventListener('mousedown', (event) => {
    if (event.button !== 0) return;
    beginSwipe(event.clientX, event.clientY);
  });

  heroSlider.addEventListener('mousemove', (event) => {
    trackSwipe(event.clientX);
  });

  heroSlider.addEventListener('mouseup', (event) => {
    endSwipe(event.clientX, event.clientY);
  });

  heroSlider.addEventListener('mouseleave', (event) => {
    if (!isPointerActive) return;
    endSwipe(event.clientX, event.clientY);
  });
}

(() => {
  const filter = document.querySelector('[data-home-products-filter]');
  const wrapper = document.querySelector('[data-home-products-wrapper]');
  const viewAllLink = document.querySelector('[data-home-products-view-all]');
  const trigger = document.querySelector('[data-home-products-trigger]');
  const arrow = document.querySelector('[data-home-products-arrow]');
  const menu = document.querySelector('[data-home-products-menu]');
  const label = document.querySelector('[data-home-products-label]');
  const options = Array.from(document.querySelectorAll('[data-home-products-option]'));

  if (!filter || !wrapper || !trigger || !arrow || !menu || !label || !options.length) return;

  let requestIndex = 0;
  let selectedOption = options.find((option) => option.classList.contains('is-active')) || options[0];

  const setOpen = (open) => {
    filter.classList.toggle('is-open', open);
    menu.hidden = !open;
    trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    arrow.setAttribute('aria-expanded', open ? 'true' : 'false');
  };

  const syncSelectionState = (option) => {
    selectedOption = option;
    label.textContent = option.dataset.label || option.textContent.trim();
    filter.dataset.selectedType = option.dataset.type || 'featured';
    filter.dataset.selectedCategoryId = option.dataset.categoryId || '';
    filter.dataset.selectedViewAllUrl = option.dataset.viewAllUrl || '';

    options.forEach((item) => {
      const active = item === option;
      item.classList.toggle('is-active', active);
      item.setAttribute('aria-selected', active ? 'true' : 'false');
    });
  };

  const syncViewAllLink = () => {
    if (!viewAllLink) return;
    const url = filter.dataset.selectedViewAllUrl;

    if (url) {
      viewAllLink.href = url;
      viewAllLink.removeAttribute('aria-disabled');
      viewAllLink.style.opacity = '1';
      return;
    }

    viewAllLink.href = '#';
    viewAllLink.setAttribute('aria-disabled', 'true');
    viewAllLink.style.opacity = '.45';
  };

  const setLoadingState = () => {
    const label = wrapper.dataset.loadingLabel || filter.dataset.loadingLabel || 'Loading...';
    wrapper.innerHTML = `
      <div class="home-products-loading">
        <div class="home-products-loading__pill">
          <span class="home-products-loading__spinner" aria-hidden="true"></span>
          <span>${label}</span>
        </div>
      </div>
    `;
  };

  const renderFallback = (message) => {
    wrapper.innerHTML = `
      <div class="text-center py-16 border reveal" style="border-color:var(--line-soft)">
        <p class="text-xl font-black mb-3">${message}</p>
      </div>
    `;
  };

  const loadProducts = async () => {
    const type = filter.dataset.selectedType || 'featured';
    const categoryId = filter.dataset.selectedCategoryId;
    const endpoint = new URL(filter.dataset.endpoint, window.location.origin);
    const currentRequest = ++requestIndex;

    endpoint.searchParams.set('type', type);

    if (type === 'category' && categoryId) {
      endpoint.searchParams.set('category_id', categoryId);
    }

    syncViewAllLink();
    setLoadingState();

    try {
      const response = await fetch(endpoint.toString(), {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
      });

      if (!response.ok) {
        throw new Error('Request failed');
      }

      const payload = await response.json();

      if (currentRequest !== requestIndex) {
        return;
      }

      wrapper.innerHTML = payload.html || '';

      if (payload.empty && payload.empty_title) {
        renderFallback(payload.empty_title);
      }
    } catch (error) {
      if (currentRequest !== requestIndex) {
        return;
      }

      renderFallback('{{ addslashes(__('storefront.home.no_featured_title')) }}');
    }
  };

  const toggleMenu = () => setOpen(menu.hidden);

  syncSelectionState(selectedOption);
  syncViewAllLink();

  trigger.addEventListener('click', toggleMenu);
  arrow.addEventListener('click', toggleMenu);

  options.forEach((option) => {
    option.addEventListener('click', () => {
      syncSelectionState(option);
      syncViewAllLink();
      setOpen(false);
      loadProducts();
    });
  });

  document.addEventListener('click', (event) => {
    if (!filter.contains(event.target)) {
      setOpen(false);
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      setOpen(false);
    }
  });
})();
</script>
@endpush

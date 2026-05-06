@php($title = ($frontendBrand['name'] ?? config('app.name')) . ' - ' . __('storefront.common.products'))

@extends('frontend.layouts.app')

@section('content')
<div class="catalog-shell" style="padding-top:72px">
  <section class="catalog-hero">
    <div class="max-w-7xl mx-auto px-6 py-10 md:py-14">
      <div class="catalog-hero__header">
        <div>
          <div class="divider"></div>
          <h1 class="catalog-hero__title">{{ __('storefront.catalog.title') }}</h1>
          <p class="catalog-hero__copy">{{ __('storefront.catalog.results_copy') }}</p>
        </div>
        <div class="catalog-hero__summary">
          <span class="catalog-pill">
            <i class='bx bx-package'></i>
            <span>{{ __('storefront.catalog.count', ['count' => $products->total()]) }}</span>
          </span>
          @if ($selectedCategory !== '')
            @php($activeCategory = $filterCategories->firstWhere('slug', $selectedCategory))
            <span class="catalog-pill catalog-pill--muted">
              <i class='bx bx-category'></i>
              <span>{{ $activeCategory?->name ?? __('storefront.catalog.category') }}</span>
            </span>
          @endif
        </div>
      </div>

      <form method="GET" action="{{ route('storefront.catalog') }}" class="catalog-search">
        <div class="catalog-search__icon">
          <i class='bx bx-search'></i>
        </div>
        <input
          type="text"
          name="search"
          class="catalog-search__input"
          placeholder="{{ __('storefront.catalog.search_placeholder') }}"
          value="{{ $searchTerm }}"
        >
        <button type="submit" class="catalog-search__submit">
          <span>{{ __('storefront.common.search') }}</span>
        </button>

        @if ($selectedCategory !== '')
          <input type="hidden" name="category" value="{{ $selectedCategory }}">
        @endif
        @if ($selectedSort !== '')
          <input type="hidden" name="sort" value="{{ $selectedSort }}">
        @endif
        @if ($selectedMinPrice !== '')
          <input type="hidden" name="min_price" value="{{ $selectedMinPrice }}">
        @endif
        @if ($selectedMaxPrice !== '')
          <input type="hidden" name="max_price" value="{{ $selectedMaxPrice }}">
        @endif
        @foreach ($selectedSizes as $selectedSize)
          <input type="hidden" name="sizes[]" value="{{ $selectedSize }}">
        @endforeach
        @foreach ($selectedColors as $selectedColor)
          <input type="hidden" name="colors[]" value="{{ $selectedColor }}">
        @endforeach
      </form>
    </div>
  </section>

  <div class="max-w-7xl mx-auto px-6 py-8 md:py-12">
    <div class="catalog-layout">
      <aside class="catalog-sidebar hidden lg:flex">
        <form method="GET" action="{{ route('storefront.catalog') }}" class="catalog-filter-panel">
          <input type="hidden" name="search" value="{{ $searchTerm }}">
          <input type="hidden" name="sort" value="{{ $selectedSort }}">

          <div class="catalog-filter-card">
            <div class="catalog-filter-card__header">
              <h2>{{ __('storefront.catalog.category') }}</h2>
              <i class='bx bx-category-alt'></i>
            </div>
            <div class="catalog-filter-stack">
              <label class="catalog-check">
                <input type="radio" name="category" value="" @checked($selectedCategory === '')>
                <span>{{ __('storefront.common.all') }}</span>
              </label>
              @foreach ($filterCategories as $category)
                <label class="catalog-check">
                  <input type="radio" name="category" value="{{ $category->slug }}" @checked($selectedCategory === $category->slug)>
                  <span>{{ $category->name }}</span>
                  <small>{{ $category->products_count }}</small>
                </label>
              @endforeach
            </div>
          </div>

          <div class="catalog-filter-card">
            <div class="catalog-filter-card__header">
              <h2>{{ __('storefront.catalog.price_range') }}</h2>
              <i class='bx bx-wallet'></i>
            </div>
            <div class="catalog-price-grid">
              <label class="catalog-field">
                <span>{{ __('storefront.catalog.min_price') }}</span>
                <input
                  type="number"
                  step="0.01"
                  min="{{ $priceRange['min'] }}"
                  max="{{ $priceRange['max'] }}"
                  name="min_price"
                  value="{{ $selectedMinPrice }}"
                  placeholder="{{ number_format($priceRange['min'], 2, '.', '') }}"
                  class="input-field"
                >
              </label>
              <label class="catalog-field">
                <span>{{ __('storefront.catalog.max_price') }}</span>
                <input
                  type="number"
                  step="0.01"
                  min="{{ $priceRange['min'] }}"
                  max="{{ $priceRange['max'] }}"
                  name="max_price"
                  value="{{ $selectedMaxPrice }}"
                  placeholder="{{ number_format($priceRange['max'], 2, '.', '') }}"
                  class="input-field"
                >
              </label>
            </div>
          </div>

          <div class="catalog-filter-card">
            <div class="catalog-filter-card__header">
              <h2>{{ __('storefront.common.size') }}</h2>
              <i class='bx bx-ruler'></i>
            </div>
            <div class="catalog-sizes">
              @foreach ($sizeOptions as $sizeOption)
                <label class="catalog-size-chip">
                  <input type="checkbox" name="sizes[]" value="{{ $sizeOption }}" @checked(in_array($sizeOption, $selectedSizes, true))>
                  <span>{{ $sizeOption }}</span>
                </label>
              @endforeach
            </div>
          </div>

          <div class="catalog-filter-card">
            <div class="catalog-filter-card__header">
              <h2>{{ app()->getLocale() === 'ar' ? 'اللون' : 'Color' }}</h2>
              <i class='bx bx-palette'></i>
            </div>
            <div class="catalog-sizes">
              @foreach ($colorOptions as $colorOption)
                <label class="catalog-size-chip">
                  <input type="checkbox" name="colors[]" value="{{ $colorOption }}" @checked(in_array($colorOption, $selectedColors, true))>
                  <span>{{ $colorOption }}</span>
                </label>
              @endforeach
            </div>
          </div>

          <div class="catalog-filter-actions">
            <button class="btn-primary w-full" type="submit"><span>{{ __('storefront.common.apply') }}</span></button>
            <a href="{{ route('storefront.catalog', ['locale' => app()->getLocale()]) }}" class="catalog-reset">{{ __('storefront.catalog.reset_filters') }}</a>
          </div>
        </form>
      </aside>

      <div class="catalog-content">
        <div class="catalog-toolbar">
          <div class="catalog-toolbar__left">
            <button class="catalog-mobile-filter lg:hidden" type="button" onclick="toggleFilterDrawer()">
              <i class='bx bx-slider-alt'></i>
              <span>{{ __('storefront.catalog.filter_button') }}</span>
            </button>
            <p class="catalog-toolbar__count">{{ __('storefront.catalog.count', ['count' => $products->total()]) }}</p>
          </div>

          <div class="catalog-toolbar__right">
            <form method="GET" action="{{ route('storefront.catalog') }}">
              <input type="hidden" name="search" value="{{ $searchTerm }}">
              <input type="hidden" name="category" value="{{ $selectedCategory }}">
              <input type="hidden" name="min_price" value="{{ $selectedMinPrice }}">
              <input type="hidden" name="max_price" value="{{ $selectedMaxPrice }}">
              @foreach ($selectedSizes as $selectedSize)
                <input type="hidden" name="sizes[]" value="{{ $selectedSize }}">
              @endforeach
              @foreach ($selectedColors as $selectedColor)
                <input type="hidden" name="colors[]" value="{{ $selectedColor }}">
              @endforeach
              <label class="catalog-sort">
                {{-- <span>{{ __('storefront.catalog.sort_by') }}</span> --}}
                <select class="sort-select" name="sort" onchange="this.form.submit()">
                  <option value="featured" @selected($selectedSort === 'featured')>{{ __('storefront.catalog.sort_featured') }}</option>
                  <option value="price-low" @selected($selectedSort === 'price-low')>{{ __('storefront.catalog.sort_price_low') }}</option>
                  <option value="price-high" @selected($selectedSort === 'price-high')>{{ __('storefront.catalog.sort_price_high') }}</option>
                  <option value="newest" @selected($selectedSort === 'newest')>{{ __('storefront.catalog.sort_newest') }}</option>
                </select>
              </label>
            </form>

            <div class="flex gap-2 items-center">
              <button class="view-toggle active" id="gridView" onclick="setView('grid')" type="button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
              </button>
              <button class="view-toggle" id="listView" onclick="setView('list')" type="button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
              </button>
            </div>
          </div>
        </div>

        @if ($selectedSizes !== [] || $selectedColors !== [] || $selectedCategory !== '' || $selectedMinPrice !== '' || $selectedMaxPrice !== '' || $searchTerm !== '')
          <div class="catalog-active-filters">
            @if ($searchTerm !== '')
              <span class="catalog-pill catalog-pill--muted"><i class='bx bx-search'></i>{{ $searchTerm }}</span>
            @endif
            @if ($selectedCategory !== '')
              <span class="catalog-pill catalog-pill--muted"><i class='bx bx-category'></i>{{ $filterCategories->firstWhere('slug', $selectedCategory)?->name }}</span>
            @endif
            @if ($selectedMinPrice !== '' || $selectedMaxPrice !== '')
              <span class="catalog-pill catalog-pill--muted"><i class='bx bx-wallet'></i>{{ __('storefront.catalog.price_label', ['min' => $selectedMinPrice !== '' ? $selectedMinPrice : '0', 'max' => $selectedMaxPrice !== '' ? $selectedMaxPrice : '∞']) }}</span>
            @endif
            @foreach ($selectedSizes as $selectedSize)
              <span class="catalog-pill catalog-pill--muted"><i class='bx bx-ruler'></i>{{ $selectedSize }}</span>
            @endforeach
            @foreach ($selectedColors as $selectedColor)
              <span class="catalog-pill catalog-pill--muted"><i class='bx bx-palette'></i>{{ $selectedColor }}</span>
            @endforeach
          </div>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4" id="productsGrid">
          @forelse ($products as $product)
            <x-frontend.product-card :product="$product" />
          @empty
            <div class="col-span-full catalog-empty">
              <i class='bx bx-search-alt-2'></i>
              <p class="catalog-empty__title">{{ __('storefront.catalog.empty_title') }}</p>
              <p class="catalog-empty__copy">{{ __('storefront.catalog.empty_description') }}</p>
            </div>
          @endforelse
        </div>

        @if ($products->hasPages())
          <div class="mt-12">{{ $products->links() }}</div>
        @endif
      </div>
    </div>
  </div>
</div>

<div class="modal-overlay catalog-filter-drawer" id="filterDrawer">
  <div class="modal-box catalog-filter-drawer__box" style="max-width:460px">
    <div class="catalog-filter-drawer__inner">
      <div class="catalog-filter-drawer__header flex items-center justify-between mb-6">
        <h2 class="text-xl font-black">{{ __('storefront.catalog.drawer_title') }}</h2>
        <button type="button" onclick="toggleFilterDrawer()" style="color:var(--gray-light)">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>

      <form method="GET" action="{{ route('storefront.catalog') }}" class="catalog-filter-drawer__form space-y-5">
        <input type="hidden" name="search" value="{{ $searchTerm }}">

        <div>
          {{-- <label class="text-xs font-bold block mb-2" style="color:var(--gray-light)">{{ __('storefront.catalog.sort_by') }}</label> --}}
          <select class="sort-select" name="sort">
            <option value="featured" @selected($selectedSort === 'featured')>{{ __('storefront.catalog.sort_featured') }}</option>
            <option value="price-low" @selected($selectedSort === 'price-low')>{{ __('storefront.catalog.sort_price_low') }}</option>
            <option value="price-high" @selected($selectedSort === 'price-high')>{{ __('storefront.catalog.sort_price_high') }}</option>
            <option value="newest" @selected($selectedSort === 'newest')>{{ __('storefront.catalog.sort_newest') }}</option>
          </select>
        </div>

        <div>
          <label class="text-xs font-bold block mb-2" style="color:var(--gray-light)">{{ __('storefront.catalog.category') }}</label>
          <div class="space-y-3">
            <label class="catalog-check">
              <input type="radio" name="category" value="" @checked($selectedCategory === '')>
              <span>{{ __('storefront.common.all') }}</span>
            </label>
            @foreach ($filterCategories as $category)
              <label class="catalog-check">
                <input type="radio" name="category" value="{{ $category->slug }}" @checked($selectedCategory === $category->slug)>
                <span>{{ $category->name }}</span>
                <small>{{ $category->products_count }}</small>
              </label>
            @endforeach
          </div>
        </div>

        <div>
          <label class="text-xs font-bold block mb-2" style="color:var(--gray-light)">{{ __('storefront.catalog.price_range') }}</label>
          <div class="catalog-price-grid">
            <label class="catalog-field">
              <span>{{ __('storefront.catalog.min_price') }}</span>
              <input type="number" step="0.01" min="{{ $priceRange['min'] }}" max="{{ $priceRange['max'] }}" name="min_price" value="{{ $selectedMinPrice }}" class="input-field">
            </label>
            <label class="catalog-field">
              <span>{{ __('storefront.catalog.max_price') }}</span>
              <input type="number" step="0.01" min="{{ $priceRange['min'] }}" max="{{ $priceRange['max'] }}" name="max_price" value="{{ $selectedMaxPrice }}" class="input-field">
            </label>
          </div>
        </div>

        <div>
          <label class="text-xs font-bold block mb-2" style="color:var(--gray-light)">{{ __('storefront.common.size') }}</label>
          <div class="catalog-sizes">
            @foreach ($sizeOptions as $sizeOption)
              <label class="catalog-size-chip">
                <input type="checkbox" name="sizes[]" value="{{ $sizeOption }}" @checked(in_array($sizeOption, $selectedSizes, true))>
                <span>{{ $sizeOption }}</span>
              </label>
            @endforeach
          </div>
        </div>

        <div>
          <label class="text-xs font-bold block mb-2" style="color:var(--gray-light)">{{ app()->getLocale() === 'ar' ? 'اللون' : 'Color' }}</label>
          <div class="catalog-sizes">
            @foreach ($colorOptions as $colorOption)
              <label class="catalog-size-chip">
                <input type="checkbox" name="colors[]" value="{{ $colorOption }}" @checked(in_array($colorOption, $selectedColors, true))>
                <span>{{ $colorOption }}</span>
              </label>
            @endforeach
          </div>
        </div>

        <button class="btn-primary w-full" type="submit"><span>{{ __('storefront.common.apply') }}</span></button>
      </form>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  .catalog-shell{background:linear-gradient(180deg,var(--gray-dark) 0%,var(--black) 320px);}
  .catalog-hero{border-bottom:1px solid var(--line-soft);}
  .catalog-hero__header{display:flex;flex-direction:column;gap:18px;margin-bottom:22px;}
  .catalog-hero__title{font-size:clamp(2rem,4vw,3.4rem);font-weight:900;letter-spacing:-0.04em;line-height:1;}
  .catalog-hero__copy{margin-top:8px;color:var(--gray-light);max-width:620px;line-height:1.8;}
  .catalog-hero__summary{display:flex;flex-wrap:wrap;gap:10px;}
  .catalog-pill{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border:1px solid var(--line-soft);background:rgb(var(--white-rgb) / .06);font-size:.86rem;font-weight:800;}
  .catalog-pill i{font-size:1rem;line-height:1;}
  .catalog-pill--muted{color:var(--gray-light);}
  .catalog-search{display:flex;align-items:center;gap:12px;padding:12px;border:1px solid var(--line-soft);background:rgb(var(--white-rgb) / .05);box-shadow:var(--panel-shadow-soft);}
  .catalog-search__icon{width:46px;height:46px;display:flex;align-items:center;justify-content:center;border:1px solid var(--line-soft);background:rgb(var(--white-rgb) / .03);color:var(--gray-light);font-size:1.25rem;flex:none;}
  .catalog-search__input{flex:1;min-width:0;background:transparent;border:0;outline:none;color:var(--white);font-size:1rem;}
  .catalog-search__submit{border:1px solid var(--line-strong);background:var(--white);color:var(--black);padding:12px 18px;font-weight:900;flex:none;}
  .catalog-layout{display:grid;grid-template-columns:minmax(0,1fr);gap:24px;}
  .catalog-sidebar{align-self:start;}
  .catalog-filter-panel{display:flex;flex-direction:column;gap:18px;position:sticky;top:110px;}
  .catalog-filter-card{padding:20px;border:1px solid var(--line-soft);background:linear-gradient(180deg,rgb(var(--surface-rgb) / .94),rgb(var(--surface-alt-rgb) / .72));}
  .catalog-filter-card__header{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;}
  .catalog-filter-card__header h2{font-size:.95rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;}
  .catalog-filter-card__header i{font-size:1.2rem;color:var(--gray-light);}
  .catalog-filter-stack{display:flex;flex-direction:column;gap:10px;max-height:320px;overflow:auto;padding-inline-end:4px;}
  .catalog-check{display:flex;align-items:center;gap:10px;padding:10px 12px;border:1px solid var(--line-soft);background:rgb(var(--white-rgb) / .03);color:var(--white);cursor:pointer;}
  .catalog-check small{margin-inline-start:auto;color:var(--gray-light);font-size:.8rem;}
  .catalog-check input{accent-color:#f5f5f0;}
  .catalog-price-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;}
  .catalog-field{display:flex;flex-direction:column;gap:8px;}
  .catalog-field span{font-size:.78rem;font-weight:800;color:var(--gray-light);letter-spacing:.06em;text-transform:uppercase;}
  .catalog-sizes{display:flex;flex-wrap:wrap;gap:10px;}
  .catalog-size-chip{position:relative;display:inline-flex;cursor:pointer;}
  .catalog-size-chip input{position:absolute;inset:0;opacity:0;pointer-events:none;}
  .catalog-size-chip span{display:inline-flex;align-items:center;justify-content:center;min-width:54px;padding:11px 14px;border:1px solid var(--line-soft);background:rgb(var(--white-rgb) / .03);font-weight:900;transition:all .2s ease;}
  .catalog-size-chip input:checked + span{background:var(--white);color:var(--black);border-color:var(--white);}
  .catalog-filter-actions{display:flex;flex-direction:column;gap:12px;}
  .catalog-reset{text-align:center;color:var(--gray-light);font-size:.92rem;text-decoration:underline;text-underline-offset:4px;}
  .catalog-content{min-width:0;}
  .catalog-toolbar{display:flex;flex-direction:column;gap:14px;margin-bottom:18px;}
  .catalog-toolbar__left,.catalog-toolbar__right{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;}
  .catalog-toolbar__count{color:var(--gray-light);font-size:.95rem;}
  .catalog-mobile-filter{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border:1px solid var(--line-soft);background:rgb(var(--white-rgb) / .05);font-weight:800;}
  .catalog-mobile-filter i{font-size:1.1rem;}
  .catalog-sort{display:flex;align-items:center;gap:10px;color:var(--gray-light);font-size:.9rem;font-weight:800;}
  .catalog-sort .sort-select{min-width:180px;}
  .catalog-active-filters{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:18px;}
  .catalog-empty{padding:80px 20px;text-align:center;border:1px solid var(--line-soft);background:rgb(var(--white-rgb) / .03);}
  .catalog-empty i{font-size:2.2rem;color:var(--gray-light);margin-bottom:10px;display:inline-block;}
  .catalog-empty__title{font-size:1.6rem;font-weight:900;margin-bottom:8px;}
  .catalog-empty__copy{color:var(--gray-light);max-width:560px;margin:0 auto;line-height:1.8;}
  .catalog-filter-drawer{padding:20px;overflow-y:auto;-webkit-overflow-scrolling:touch;align-items:flex-start;}
  .catalog-filter-drawer__box{width:min(460px,100%);margin:auto;max-height:calc(100dvh - 40px);overflow:hidden;}
  .catalog-filter-drawer__inner{display:flex;flex-direction:column;max-height:calc(100dvh - 40px);}
  .catalog-filter-drawer__header{padding:24px 24px 0;flex:none;}
  .catalog-filter-drawer__form{padding:0 24px 24px;overflow-y:auto;-webkit-overflow-scrolling:touch;min-height:0;}
  @media (min-width: 1024px){
    .catalog-layout{grid-template-columns:300px minmax(0,1fr);}
    .catalog-hero__header{flex-direction:row;align-items:flex-end;justify-content:space-between;}
  }
  @media (max-width: 767px){
    .catalog-search{padding:10px;gap:10px;}
    .catalog-search__icon{width:42px;height:42px;}
    .catalog-search__submit{padding:11px 14px;font-size:.88rem;}
    .catalog-price-grid{grid-template-columns:1fr;}
    .catalog-sort{width:100%;justify-content:space-between;}
    .catalog-sort .sort-select{min-width:0;width:100%;}
    .catalog-filter-drawer{padding:12px;}
    .catalog-filter-drawer__box,.catalog-filter-drawer__inner{max-height:calc(100dvh - 24px);}
    .catalog-filter-drawer__header{padding:18px 18px 0;margin-bottom:18px;}
    .catalog-filter-drawer__form{padding:0 18px 18px;}
  }
</style>
@endpush

@push('scripts')
<script>
let currentView = 'grid';

function setView(view) {
  currentView = view;
  document.getElementById('gridView')?.classList.toggle('active', view === 'grid');
  document.getElementById('listView')?.classList.toggle('active', view === 'list');
  const grid = document.getElementById('productsGrid');
  if (!grid) return;
  grid.className = view === 'grid' ? 'grid grid-cols-2 md:grid-cols-3 gap-4' : 'flex flex-col gap-4';
  grid.querySelectorAll('.product-card').forEach((card) => {
    card.style.display = view === 'list' ? 'flex' : 'block';
    card.style.alignItems = view === 'list' ? 'center' : '';
  });
}

function toggleFilterDrawer() {
  const drawer = document.getElementById('filterDrawer');
  if (!drawer) return;

  const isOpen = drawer.classList.toggle('open');
  document.body.style.overflow = isOpen ? 'hidden' : '';
}

document.getElementById('filterDrawer')?.addEventListener('click', function (event) {
  if (event.target === this) {
    toggleFilterDrawer();
  }
});
</script>
@endpush

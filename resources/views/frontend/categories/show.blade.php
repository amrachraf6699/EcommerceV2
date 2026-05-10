@php($title = ($frontendBrand['name'] ?? config('app.name')) . ' - ' . $category->name)

@extends('frontend.layouts.app')

@section('content')
<section class="px-6 pb-16" style="padding-top:120px;background:var(--black)">
  <div class="max-w-7xl mx-auto">
    @if ($products->count() > 0)
      @include('frontend.partials.product-collection', ['products' => $products, 'showOverlay' => false])
      <div class="mt-12">
        {{ $products->links() }}
      </div>
    @else
      <div class="border p-8 text-center" style="border-color:var(--line-soft);background:var(--gray-dark)" data-empty-products-state>
        <p class="text-xl font-black mb-3">{{ __('storefront.category_show.no_products') }}</p>
        <p style="color:var(--gray-light)">{{ __('storefront.category_show.no_products_hint') }}</p>
        <div class="flex flex-wrap items-center justify-center gap-4 mt-8">
          @if ($alternativeCategory)
            <a href="{{ route('storefront.categories.show', ['category' => $alternativeCategory->slug]) }}" class="btn-outline">
              <span>{{ __('storefront.category_show.browse_another_category') }}</span>
            </a>
          @endif
          <button
            type="button"
            class="btn-primary"
            data-load-fallback-products
            data-endpoint="{{ route('storefront.categories.fallback-products', ['category' => $category->slug]) }}"
          >
            <span data-load-fallback-label>{{ __('storefront.category_show.load_products') }}</span>
          </button>
        </div>
      </div>
      <div class="hidden" data-fallback-products-panel>
        <div class="text-center mb-14 reveal">
          <div class="divider" style="margin:0 auto 16px"></div>
          <h3 class="text-2xl font-black mb-3" style="letter-spacing:-0.02em" data-fallback-products-title></h3>
        </div>
        <div data-fallback-products-target></div>
      </div>
    @endif
  </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const loadButton = document.querySelector('[data-load-fallback-products]');
  if (!loadButton) {
    return;
  }

  const emptyState = document.querySelector('[data-empty-products-state]');
  const panel = document.querySelector('[data-fallback-products-panel]');
  const panelTarget = document.querySelector('[data-fallback-products-target]');
  const panelTitle = document.querySelector('[data-fallback-products-title]');
  const label = loadButton.querySelector('[data-load-fallback-label]');
  const defaultLabel = label ? label.textContent : '';
  const loadingLabel = @json(__('storefront.category_show.loading_products'));

  loadButton.addEventListener('click', async () => {
    if (loadButton.disabled) {
      return;
    }

    loadButton.disabled = true;
    if (label) {
      label.textContent = loadingLabel;
    }

    try {
      const response = await fetch(loadButton.dataset.endpoint, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
      });

      const payload = await response.json();

      if (!response.ok || !payload.html) {
        throw new Error(payload.message || 'Unable to load products.');
      }

      if (panelTitle && payload.title) {
        panelTitle.textContent = payload.title;
      }

      if (panelTarget) {
        panelTarget.innerHTML = payload.html;
      }

      emptyState?.classList.add('hidden');
      panel?.classList.remove('hidden');
    } catch (error) {
      showToast(error.message || 'Unable to load products right now.', 'error');
      loadButton.disabled = false;
      if (label) {
        label.textContent = defaultLabel;
      }
      return;
    }

    if (label) {
      label.textContent = defaultLabel;
    }
  });
});
</script>
@endpush

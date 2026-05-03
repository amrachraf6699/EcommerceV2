@php($title = ($frontendBrand['name'] ?? config('app.name')) . ' - ' . __('storefront.common.categories'))

@extends('frontend.layouts.app')

@section('content')
<div style="padding-top:72px;background:var(--gray-dark);border-bottom:1px solid var(--line-soft)">
  <div class="mt-6 max-w-7xl mx-auto px-6 py-16">
    <div class="divider reveal"></div>
    <h1 class="text-5xl font-black mb-4 reveal" style="letter-spacing:-0.03em">{{ __('storefront.categories_page.title') }}</h1>
    <p class="reveal" style="color:var(--gray-light);max-width:640px;font-size:16px;line-height:1.8">{{ __('storefront.categories_page.intro') }}</p>
  </div>
</div>

@if ($featuredCategories->isNotEmpty())
  <section class="py-16 px-6" style="background:var(--black)">
    <div class="max-w-7xl mx-auto">
      <div class="text-center mb-14 reveal">
        <div class="divider" style="margin:0 auto 16px"></div>
        <h2 class="text-3xl font-black mb-3" style="letter-spacing:-0.02em">{{ __('storefront.categories_page.featured_title') }}</h2>
        <p style="color:var(--gray-light)">{{ __('storefront.categories_page.featured_copy') }}</p>
      </div>

      @include('frontend.partials.category-collection', ['categories' => $featuredCategories, 'largeFirst' => true])
    </div>
  </section>
@endif

<section class="py-16 px-6" style="background:var(--gray-dark);border-top:1px solid var(--line-soft)">
  <div class="max-w-7xl mx-auto">
    <div class="flex items-end justify-between gap-6 mb-12">
      <div>
        <div class="divider reveal"></div>
        <h2 class="text-3xl md:text-4xl font-black mb-3 reveal" style="letter-spacing:-0.02em">{{ __('storefront.categories_page.all_title') }}</h2>
        <p class="reveal" style="color:var(--gray-light)">{{ __('storefront.categories_page.all_copy') }}</p>
      </div>
    </div>

    @if ($categories->isNotEmpty())
      @include('frontend.partials.category-collection', ['categories' => $categories])
    @else
      <div class="text-center py-20 border" style="border-color:var(--line-soft)">
        <p class="text-2xl font-black mb-3">{{ __('storefront.categories_page.empty_title') }}</p>
        <p style="color:var(--gray-light)">{{ __('storefront.categories_page.empty_description') }}</p>
      </div>
    @endif
  </div>
</section>
@endsection

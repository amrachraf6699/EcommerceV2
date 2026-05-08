@props([
    'category',
    'large' => false,
    'tag' => null,
    'compact' => false,
])

@php
    $height = $large ? '500px' : '240px';
    $padding = $large ? '32px' : '20px';
    $productsCount = $category->products_count ?? $category->products?->count() ?? 0;
@endphp

@if ($compact)
    <a href="{{ route('storefront.categories.show', ['category' => $category->slug]) }}" class="reveal block">
        <div class="border" style="border-color:var(--line-soft);background:var(--gray-dark);padding:10px;">
            <div class="compact-card-media" style="aspect-ratio:1 / 1;background:transparent;overflow:hidden;">
                @if ($category->image_url)
                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="compact-card-media__image w-full h-full">
                @else
                    <div class="w-full h-full flex items-center justify-center text-sm font-black text-neutral-500">{{ $category->name }}</div>
                @endif
            </div>
            <div style="padding-top:10px">
                <h2 style="font-size:13px;line-height:1.5;font-weight:800">{{ \Illuminate\Support\Str::limit($category->name, 24) }}</h2>
            </div>
        </div>
    </a>
@else
    <a href="{{ route('storefront.categories.show', ['category' => $category->slug]) }}" class="cat-hero reveal" style="height:{{ $height }};{{ $large ? 'grid-row:span 2' : '' }}">
        @if ($category->image_url)
            <img src="{{ $category->image_url }}" alt="{{ $category->name }}">
        @else
            <div class="w-full h-full flex items-center justify-center text-2xl font-black text-neutral-600">{{ $category->name }}</div>
        @endif
        <span class="cat-tag">{{ $productsCount }} {{ __('storefront.catalog.products_label') }}</span>
        <div class="cat-hero-content" style="padding:{{ $padding }}">
            <h2 class="text-{{ $large ? '4xl' : '3xl' }} font-black">{{ $category->name }}</h2>
            @if ($large && $category->description)
                <p class="text-sm mb-6 mt-3" style="color:var(--text-soft)">{{ \Illuminate\Support\Str::limit($category->description, 110) }}</p>
                <span class="btn-outline text-sm"><span>{{ __('storefront.common.shop_now') }}</span></span>
            @endif
        </div>
    </a>
@endif

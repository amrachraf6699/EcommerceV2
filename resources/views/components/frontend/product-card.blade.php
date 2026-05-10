@props([
    'product',
    'list' => false,
    'href' => null,
    'imageHeight' => null,
    'showOverlay' => true,
    'compact' => false,
])

@php
    $productHref = $href ?: route('storefront.products.show', ['product' => $product->slug]);
    $imageHeight ??= $list ? '160px' : '240px';
    $isSoldOut = (bool) ($product->display_is_sold_out ?? false);
@endphp

@if ($compact)
    <a href="{{ $productHref }}" class="block">
        <div class="border" style="border-color:var(--line-soft);background:var(--gray-dark);padding:10px;">
            <div class="compact-card-media" style="aspect-ratio:1 / 1;background:var(--gray-dark);overflow:hidden;position:relative;">
                @if ($product->primary_image_url)
                    <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" loading="lazy" class="compact-card-media__image w-full h-full">
                @else
                    <div class="w-full h-full flex items-center justify-center text-xs text-neutral-500">{{ __('storefront.common.no_image') }}</div>
                @endif

                @if ($product->display_badge)
                    <span class="badge {{ $isSoldOut ? 'badge--sold-out' : '' }}">{{ $product->display_badge }}</span>
                @endif
            </div>

            <div style="padding-top:10px">
                <p style="font-size:9px;font-weight:800;color:var(--gray-light);letter-spacing:.12em;margin-bottom:4px">{{ \Illuminate\Support\Str::limit($product->display_label, 14) }}</p>
                <h3 style="font-weight:800;font-size:12px;line-height:1.5;margin-bottom:6px">{{ \Illuminate\Support\Str::limit($product->name, 22) }}</h3>
                <x-frontend.price
                    :amount="$product->display_price"
                    :compare-amount="$product->display_compare_price"
                    wrapper-class="gap-1"
                    amount-class="font-black text-[13px]"
                    compare-class="text-[11px] line-through"
                    secondary-class="text-[11px]"
                    note-class="text-[10px]"
                    unavailable-class="text-[13px]"
                    row-class="flex flex-col gap-[3px]"
                />
            </div>
        </div>
    </a>
@else
    <a href="{{ $productHref }}" class="product-card" style="display:{{ $list ? 'flex' : 'block' }};{{ $list ? 'align-items:center;' : '' }}">
        <div class="product-img-wrap" style="height:{{ $imageHeight }};{{ $list ? 'width:200px;flex-shrink:0;' : '' }}">
            @if ($product->primary_image_url)
                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" loading="lazy">
            @else
                <div class="w-full h-full flex items-center justify-center text-sm text-neutral-500">{{ __('storefront.common.no_image') }}</div>
            @endif

            @if ($product->display_badge)
                <span class="badge {{ $isSoldOut ? 'badge--sold-out' : '' }}">{{ $product->display_badge }}</span>
            @endif

            @if ($showOverlay)
                <div class="product-overlay">
                    <button class="btn-primary flex-1 text-sm py-2" type="button" style="width:100%"><span>{{ __('storefront.common.view_product') }}</span></button>
                </div>
            @endif
        </div>

        <div style="padding:16px;{{ $list ? 'flex:1;' : '' }}">
            <p style="font-size:11px;font-weight:700;color:var(--gray-light);letter-spacing:0.12em;margin-bottom:4px">{{ $product->display_label }}</p>
            <h3 style="font-weight:700;font-size:15px;margin-bottom:8px">{{ \Illuminate\Support\Str::limit($product->name, 20) }}</h3>
            @if ($product->short_description)
                <p class="text-sm leading-7 mb-3" style="color:var(--gray-light)">{{ \Illuminate\Support\Str::limit($product->short_description, 80) }}</p>
            @endif
            <x-frontend.price
                :amount="$product->display_price"
                :compare-amount="$product->display_compare_price"
                wrapper-class="gap-1"
                amount-class="font-black text-[18px]"
                compare-class="text-[13px] line-through"
                secondary-class="text-[13px]"
                note-class="text-[11px]"
                unavailable-class="text-[18px]"
                row-class="flex items-center gap-2 flex-wrap"
            />
        </div>
    </a>
@endif

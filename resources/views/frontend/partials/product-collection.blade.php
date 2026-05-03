@props([
    'products',
    'showOverlay' => true,
])

@php
    $appearance = setting('appearance.products_appearance', 'Grid');
@endphp

@if ($appearance === 'Horizontal Scroll')
    <div class="collection-scroller product-scroller">
        @foreach ($products as $product)
            <div class="product-scroller__item">
                <x-frontend.product-card :product="$product" :show-overlay="$showOverlay" compact />
            </div>
        @endforeach
    </div>
@elseif ($appearance === 'Masonry Layout')
    <div class="product-masonry">
        @foreach ($products as $product)
            @php
                $pattern = $loop->index % 5;
                $isFeatured = $pattern === 0;
                $isTall = in_array($pattern, [2, 4], true);
                $imageHeight = $isFeatured ? '360px' : ($isTall ? '320px' : '220px');
            @endphp
            <div class="product-masonry__item {{ $isFeatured ? 'product-masonry__item--featured' : '' }} {{ $isTall ? 'product-masonry__item--tall' : '' }}">
                <x-frontend.product-card :product="$product" :show-overlay="$showOverlay" :image-height="$imageHeight" />
            </div>
        @endforeach
    </div>
@else
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach ($products as $product)
            <x-frontend.product-card :product="$product" :show-overlay="$showOverlay" />
        @endforeach
    </div>
@endif

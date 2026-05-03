@props([
    'categories',
    'largeFirst' => false,
])

@php
    $appearance = setting('appearance.categories_appearance', 'Masonry Layout');
@endphp

@if ($appearance === 'Horizontal Scroll')
    <div class="collection-scroller category-scroller">
        @foreach ($categories as $category)
            <div class="category-scroller__item">
                <x-frontend.category-card :category="$category" compact />
            </div>
        @endforeach
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
        @foreach ($categories as $category)
            <x-frontend.category-card :category="$category" :large="$largeFirst && $loop->first" />
        @endforeach
    </div>
@endif

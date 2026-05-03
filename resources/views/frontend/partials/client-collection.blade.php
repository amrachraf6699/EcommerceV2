@props([
    'clients',
])

@php
    $appearance = setting('appearance.clients_appearance', 'Grid');
@endphp

@if ($appearance === 'Horizontal Scroll')
    <div class="collection-scroller client-scroller">
        @foreach ($clients as $client)
            <div class="client-scroller__item">
                <x-frontend.client-card :client="$client" compact />
            </div>
        @endforeach
    </div>
@elseif ($appearance === 'Masonry Layout')
    <div class="client-masonry">
        @foreach ($clients as $client)
            @php
                $pattern = $loop->index % 5;
                $isFeatured = $pattern === 0;
                $isTall = in_array($pattern, [2, 4], true);
            @endphp
            <div class="client-masonry__item {{ $isFeatured ? 'client-masonry__item--featured' : '' }} {{ $isTall ? 'client-masonry__item--tall' : '' }}">
                <x-frontend.client-card :client="$client" />
            </div>
        @endforeach
    </div>
@else
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach ($clients as $client)
            <x-frontend.client-card :client="$client" />
        @endforeach
    </div>
@endif

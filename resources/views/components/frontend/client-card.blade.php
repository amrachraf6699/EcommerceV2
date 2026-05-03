@props([
    'client',
    'compact' => false,
])

@if ($compact)
    <article class="client-card client-card--compact">
        <div class="client-card__media client-card__media--compact">
            <img src="{{ asset('storage/' . $client->photo) }}" alt="{{ $client->name }}">
        </div>
        <div class="p-3 text-center">
            <h3 class="text-sm font-black leading-6">{{ $client->name }}</h3>
            @if ($client->position)
                <p class="mt-1 text-xs leading-5" style="color:var(--gray-light)">{{ $client->position }}</p>
            @endif
        </div>
    </article>
@else
    <article class="client-card">
        <div class="client-card__media">
            <img src="{{ asset('storage/' . $client->photo) }}" alt="{{ $client->name }}">
        </div>
        <div class="p-4 md:p-5 text-center">
            <h3 class="text-base md:text-lg font-black leading-7">{{ $client->name }}</h3>
            @if ($client->position)
                <p class="mt-2 text-sm leading-6" style="color:var(--gray-light)">{{ $client->position }}</p>
            @endif
        </div>
    </article>
@endif

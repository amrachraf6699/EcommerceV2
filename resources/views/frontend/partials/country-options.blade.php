@php
    $selectedCountry = $selectedCountry ?? null;
    $selectedCountries = collect($selectedCountries ?? [])
        ->filter(fn ($value) => filled($value))
        ->values();
    $isMultiple = $selectedCountries->isNotEmpty();
    $countries = app(\App\Support\StorefrontCountryCatalog::class)->countries();
@endphp

@unless ($isMultiple)
    <option value="">{{ __('storefront.account.country_placeholder') }}</option>
@endunless
@foreach ($countries as $country)
    <option value="{{ $country }}" @selected($selectedCountry === $country || $selectedCountries->contains($country))>{{ $country }}</option>
@endforeach

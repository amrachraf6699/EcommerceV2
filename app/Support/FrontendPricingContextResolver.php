<?php

namespace App\Support;

use App\Services\CurrencyDetectionService;
use App\Services\CurrencyRateService;
use Illuminate\Http\Request;

class FrontendPricingContextResolver
{
    public function __construct(
        private readonly CurrencyDetectionService $currencyDetectionService,
        private readonly CurrencyRateService $currencyRateService,
    ) {
    }

    /**
     * @return array{
     *     base_currency:string,
     *     detected_country_code:?string,
     *     detected_currency:?string,
     *     rate:?float,
     *     rate_date:?string,
     *     enabled:bool
     * }
     */
    public function resolve(Request $request): array
    {
        $baseCurrency = 'BHD';
        $detected = $this->currencyDetectionService->detect($request);
        $detectedCurrency = strtoupper((string) ($detected['currency'] ?? ''));
        $detectedCountryCode = strtoupper((string) ($detected['country_code'] ?? ''));

        if ($detectedCurrency === '' || $detectedCurrency === $baseCurrency) {
            return [
                'base_currency' => $baseCurrency,
                'detected_country_code' => $detectedCountryCode !== '' ? $detectedCountryCode : null,
                'detected_currency' => $detectedCurrency !== '' ? $detectedCurrency : null,
                'rate' => null,
                'rate_date' => null,
                'enabled' => false,
            ];
        }

        $rate = $this->currencyRateService->rate($baseCurrency, $detectedCurrency);

        return [
            'base_currency' => $baseCurrency,
            'detected_country_code' => $detectedCountryCode !== '' ? $detectedCountryCode : null,
            'detected_currency' => $detectedCurrency,
            'rate' => $rate['rate'],
            'rate_date' => $rate['date'],
            'enabled' => $rate['rate'] !== null,
        ];
    }
}

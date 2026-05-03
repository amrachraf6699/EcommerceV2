<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyRateService
{
    /**
     * @return array{rate:?float,date:?string}
     */
    public function rate(string $baseCurrency, string $quoteCurrency): array
    {
        $baseCurrency = strtoupper(trim($baseCurrency));
        $quoteCurrency = strtoupper(trim($quoteCurrency));

        if ($baseCurrency === '' || $quoteCurrency === '' || $baseCurrency === $quoteCurrency) {
            return [
                'rate' => null,
                'date' => null,
            ];
        }

        return Cache::remember(
            "storefront:currency-rate:{$baseCurrency}:{$quoteCurrency}",
            now()->addHours(12),
            function () use ($baseCurrency, $quoteCurrency): array {
                try {
                    $response = Http::timeout(3)
                        ->acceptJson()
                        ->get("https://api.frankfurter.dev/v2/rate/{$baseCurrency}/{$quoteCurrency}");

                    if (! $response->ok()) {
                        return [
                            'rate' => null,
                            'date' => null,
                        ];
                    }

                    $payload = $response->json();
                    $rate = data_get($payload, 'rate');
                    $date = data_get($payload, 'date');

                    return [
                        'rate' => is_numeric($rate) ? (float) $rate : null,
                        'date' => is_string($date) && $date !== '' ? $date : null,
                    ];
                } catch (\Throwable) {
                    return [
                        'rate' => null,
                        'date' => null,
                    ];
                }
            }
        );
    }
}

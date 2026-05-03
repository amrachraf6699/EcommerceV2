<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyDetectionService
{
    private const LOCAL_FALLBACK_IP = '196.157.6.186';
    private const LOOKUP_CACHE_TTL_HOURS = 24;

    /**
     * @var array<int, array{name:string,url:string,country_key:string,success_key:?string,success_value:mixed}>
     */
    private const FREE_PROVIDERS = [
        [
            'name' => 'ip-api',
            'url' => 'http://ip-api.com/json/%s?fields=status,countryCode',
            'country_key' => 'countryCode',
            'success_key' => 'status',
            'success_value' => 'success',
        ],
        [
            'name' => 'ipwhois',
            'url' => 'https://ipwho.is/%s',
            'country_key' => 'country_code',
            'success_key' => 'success',
            'success_value' => true,
        ],
        [
            'name' => 'ipapi',
            'url' => 'https://ipapi.co/%s/json/',
            'country_key' => 'country_code',
            'success_key' => null,
            'success_value' => null,
        ],
    ];

    /**
     * @return array{country_code:?string,currency:?string}
     */
    public function detect(Request $request): array
    {
        $ipAddress = $request->ip();
        $lookupTarget = $this->lookupTarget($ipAddress);

        if ($lookupTarget === null) {
            return [
                'country_code' => null,
                'currency' => null,
            ];
        }

        return Cache::remember(
            'storefront:currency-detection:' . md5($lookupTarget),
            now()->addHours(self::LOOKUP_CACHE_TTL_HOURS),
            function () use ($lookupTarget): array {
                try {
                    $countryCode = $this->detectCountryCode($lookupTarget);
                    $currency = strtoupper((string) config('country_currencies.map.' . $countryCode, ''));

                    return [
                        'country_code' => $countryCode !== '' ? $countryCode : null,
                        'currency' => $currency !== '' ? $currency : null,
                    ];
                } catch (\Throwable) {
                    return [
                        'country_code' => null,
                        'currency' => null,
                    ];
                }
            }
        );
    }

    private function detectCountryCode(string $lookupTarget): string
    {
        $providers = self::FREE_PROVIDERS;
        $providerCount = count($providers);
        $startIndex = $providerCount > 0 ? abs(crc32($lookupTarget)) % $providerCount : 0;

        for ($offset = 0; $offset < $providerCount; $offset++) {
            $provider = $providers[($startIndex + $offset) % $providerCount];
            $countryCode = $this->lookupCountryCode($provider, $lookupTarget);

            if ($countryCode !== null) {
                return $countryCode;
            }
        }

        return '';
    }

    /**
     * @param array{name:string,url:string,country_key:string,success_key:?string,success_value:mixed} $provider
     */
    private function lookupCountryCode(array $provider, string $lookupTarget): ?string
    {
        try {
            $response = Http::timeout(3)
                ->acceptJson()
                ->get(sprintf($provider['url'], $lookupTarget));

            if (! $response->ok()) {
                return null;
            }

            $payload = $response->json();

            if (! is_array($payload)) {
                return null;
            }

            $successKey = $provider['success_key'];

            if ($successKey !== null && data_get($payload, $successKey) !== $provider['success_value']) {
                return null;
            }

            $countryCode = strtoupper((string) data_get($payload, $provider['country_key']));

            return $countryCode !== '' ? $countryCode : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function lookupTarget(mixed $ipAddress): ?string
    {
        if (! is_string($ipAddress)) {
            return null;
        }

        if ($this->isLookupableIp($ipAddress)) {
            return $ipAddress;
        }

        return self::LOCAL_FALLBACK_IP;
    }

    private function isLookupableIp(string $ipAddress): bool
    {
        return filter_var(
            $ipAddress,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }
}

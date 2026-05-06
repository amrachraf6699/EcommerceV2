<?php

namespace App\Support;

use Illuminate\Support\Str;

class StorefrontCountryCatalog
{
    /**
     * @var array<string, string>
     */
    private const DETECTED_COUNTRY_NAMES = [
        'AL' => 'Albania',
        'AD' => 'Andorra',
        'AR' => 'Argentina',
        'AT' => 'Austria',
        'BH' => 'Bahrain',
        'BE' => 'Belgium',
        'BA' => 'Bosnia and Herzegovina',
        'BR' => 'Brazil',
        'BG' => 'Bulgaria',
        'CA' => 'Canada',
        'HR' => 'Croatia',
        'CY' => 'Cyprus',
        'CZ' => 'Czech Republic',
        'DK' => 'Denmark',
        'EG' => 'Egypt',
        'EE' => 'Estonia',
        'FI' => 'Finland',
        'FR' => 'France',
        'GE' => 'Georgia',
        'DE' => 'Germany',
        'GR' => 'Greece',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IE' => 'Ireland',
        'IT' => 'Italy',
        'KW' => 'Kuwait',
        'LV' => 'Latvia',
        'LI' => 'Liechtenstein',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MT' => 'Malta',
        'MX' => 'Mexico',
        'MD' => 'Moldova',
        'MC' => 'Monaco',
        'ME' => 'Montenegro',
        'NL' => 'Netherlands',
        'MK' => 'North Macedonia',
        'NO' => 'Norway',
        'OM' => 'Oman',
        'PA' => 'Panama',
        'PY' => 'Paraguay',
        'PE' => 'Peru',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'QA' => 'Qatar',
        'RO' => 'Romania',
        'RU' => 'Russia',
        'SM' => 'San Marino',
        'SA' => 'Saudi Arabia',
        'RS' => 'Serbia',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'ES' => 'Spain',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'TR' => 'Turkey',
        'UA' => 'Ukraine',
        'AE' => 'United Arab Emirates',
        'GB' => 'United Kingdom',
        'US' => 'United States',
        'UY' => 'Uruguay',
        'VA' => 'Vatican City',
        'VE' => 'Venezuela',
    ];

    /**
     * @var array<int, string>
     */
    private const GULF_COUNTRIES = [
        'Bahrain',
        'Kuwait',
        'Oman',
        'Qatar',
        'Saudi Arabia',
        'United Arab Emirates',
    ];

    public function countryNameFromDetectedCode(?string $countryCode): ?string
    {
        $countryCode = Str::upper(trim((string) $countryCode));

        if ($countryCode === '') {
            return null;
        }

        return self::DETECTED_COUNTRY_NAMES[$countryCode] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function detectedCountryNameMap(): array
    {
        return self::DETECTED_COUNTRY_NAMES;
    }

    public function resolveShippingZone(?string $country): ?string
    {
        $country = $this->normalizeCountry($country);

        if ($country === '') {
            return null;
        }

        if ($this->contains(self::GULF_COUNTRIES, $country)) {
            return 'gulf';
        }

        return 'others';
    }

    private function contains(array $countries, string $country): bool
    {
        foreach ($countries as $candidate) {
            if ($this->normalizeCountry($candidate) === $country) {
                return true;
            }
        }

        return false;
    }

    private function normalizeCountry(?string $country): string
    {
        return Str::lower(trim((string) $country));
    }
}

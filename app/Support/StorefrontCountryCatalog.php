<?php

namespace App\Support;

use Illuminate\Support\Str;

class StorefrontCountryCatalog
{
    /**
     * Country names accepted by the storefront, keyed to their international
     * dialing code without the leading plus sign.
     *
     * @var array<string, string>
     */
    private const COUNTRY_DIALING_CODES = [
        'Afghanistan' => '93', 'Albania' => '355', 'Algeria' => '213', 'Andorra' => '376',
        'Angola' => '244', 'Antigua and Barbuda' => '1268', 'Argentina' => '54', 'Armenia' => '374',
        'Australia' => '61', 'Austria' => '43', 'Azerbaijan' => '994', 'Bahamas' => '1242',
        'Bahrain' => '973', 'Bangladesh' => '880', 'Barbados' => '1246', 'Belarus' => '375',
        'Belgium' => '32', 'Belize' => '501', 'Benin' => '229', 'Bhutan' => '975',
        'Bolivia' => '591', 'Bosnia and Herzegovina' => '387', 'Botswana' => '267', 'Brazil' => '55',
        'Brunei' => '673', 'Bulgaria' => '359', 'Burkina Faso' => '226', 'Burundi' => '257',
        'Cabo Verde' => '238', 'Cambodia' => '855', 'Cameroon' => '237', 'Canada' => '1',
        'Central African Republic' => '236', 'Chad' => '235', 'Chile' => '56', 'China' => '86',
        'Colombia' => '57', 'Comoros' => '269', 'Congo' => '242', 'Costa Rica' => '506',
        'Croatia' => '385', 'Cuba' => '53', 'Cyprus' => '357', 'Czech Republic' => '420',
        'Democratic Republic of the Congo' => '243', 'Denmark' => '45', 'Djibouti' => '253',
        'Dominica' => '1767', 'Dominican Republic' => '1809', 'Ecuador' => '593', 'Egypt' => '20',
        'El Salvador' => '503', 'Equatorial Guinea' => '240', 'Eritrea' => '291', 'Estonia' => '372',
        'Eswatini' => '268', 'Ethiopia' => '251', 'Fiji' => '679', 'Finland' => '358',
        'France' => '33', 'Gabon' => '241', 'Gambia' => '220', 'Georgia' => '995',
        'Germany' => '49', 'Ghana' => '233', 'Greece' => '30', 'Grenada' => '1473',
        'Guatemala' => '502', 'Guinea' => '224', 'Guinea-Bissau' => '245', 'Guyana' => '592',
        'Haiti' => '509', 'Honduras' => '504', 'Hungary' => '36', 'Iceland' => '354',
        'India' => '91', 'Indonesia' => '62', 'Iran' => '98', 'Iraq' => '964',
        'Ireland' => '353', 'Israel' => '972', 'Italy' => '39', 'Ivory Coast' => '225',
        'Jamaica' => '1876', 'Japan' => '81', 'Jordan' => '962', 'Kazakhstan' => '7',
        'Kenya' => '254', 'Kiribati' => '686', 'Kuwait' => '965', 'Kyrgyzstan' => '996',
        'Laos' => '856', 'Latvia' => '371', 'Lebanon' => '961', 'Lesotho' => '266',
        'Liberia' => '231', 'Libya' => '218', 'Liechtenstein' => '423', 'Lithuania' => '370',
        'Luxembourg' => '352', 'Madagascar' => '261', 'Malawi' => '265', 'Malaysia' => '60',
        'Maldives' => '960', 'Mali' => '223', 'Malta' => '356', 'Marshall Islands' => '692',
        'Mauritania' => '222', 'Mauritius' => '230', 'Mexico' => '52', 'Micronesia' => '691',
        'Moldova' => '373', 'Monaco' => '377', 'Mongolia' => '976', 'Montenegro' => '382',
        'Morocco' => '212', 'Mozambique' => '258', 'Myanmar' => '95', 'Namibia' => '264',
        'Nauru' => '674', 'Nepal' => '977', 'Netherlands' => '31', 'New Zealand' => '64',
        'Nicaragua' => '505', 'Niger' => '227', 'Nigeria' => '234', 'North Korea' => '850',
        'North Macedonia' => '389', 'Norway' => '47', 'Oman' => '968', 'Pakistan' => '92',
        'Palau' => '680', 'Palestine' => '970', 'Panama' => '507', 'Papua New Guinea' => '675',
        'Paraguay' => '595', 'Peru' => '51', 'Philippines' => '63', 'Poland' => '48',
        'Portugal' => '351', 'Qatar' => '974', 'Romania' => '40', 'Russia' => '7',
        'Rwanda' => '250', 'Saint Kitts and Nevis' => '1869', 'Saint Lucia' => '1758',
        'Saint Vincent and the Grenadines' => '1784', 'Samoa' => '685', 'San Marino' => '378',
        'Sao Tome and Principe' => '239', 'Saudi Arabia' => '966', 'Senegal' => '221',
        'Serbia' => '381', 'Seychelles' => '248', 'Sierra Leone' => '232', 'Singapore' => '65',
        'Slovakia' => '421', 'Slovenia' => '386', 'Solomon Islands' => '677', 'Somalia' => '252',
        'South Africa' => '27', 'South Korea' => '82', 'South Sudan' => '211', 'Spain' => '34',
        'Sri Lanka' => '94', 'Sudan' => '249', 'Suriname' => '597', 'Sweden' => '46',
        'Switzerland' => '41', 'Syria' => '963', 'Taiwan' => '886', 'Tajikistan' => '992',
        'Tanzania' => '255', 'Thailand' => '66', 'Timor-Leste' => '670', 'Togo' => '228',
        'Tonga' => '676', 'Trinidad and Tobago' => '1868', 'Tunisia' => '216', 'Turkey' => '90',
        'Turkmenistan' => '993', 'Tuvalu' => '688', 'Uganda' => '256', 'Ukraine' => '380',
        'United Arab Emirates' => '971', 'United Kingdom' => '44', 'United States' => '1',
        'Uruguay' => '598', 'Uzbekistan' => '998', 'Vanuatu' => '678', 'Vatican City' => '379',
        'Venezuela' => '58', 'Vietnam' => '84', 'Yemen' => '967', 'Zambia' => '260', 'Zimbabwe' => '263',
    ];

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

    /**
     * @return list<string>
     */
    public function countries(): array
    {
        return array_keys(self::COUNTRY_DIALING_CODES);
    }

    /**
     * @return array<string, string>
     */
    public function countryDialingCodes(): array
    {
        return self::COUNTRY_DIALING_CODES;
    }

    public function dialingCodeForCountry(?string $country): ?string
    {
        $country = trim((string) $country);

        return self::COUNTRY_DIALING_CODES[$country] ?? null;
    }

    public function formatPhone(string $country, string $localPhone): string
    {
        $dialingCode = $this->dialingCodeForCountry($country);

        if (! $dialingCode) {
            return trim($localPhone);
        }

        return '+' . $dialingCode . preg_replace('/\\D+/', '', $localPhone);
    }

    public function localPhoneForCountry(?string $country, ?string $phone): string
    {
        $phone = trim((string) $phone);
        $dialingCode = $this->dialingCodeForCountry($country);

        if (! $dialingCode) {
            return $phone;
        }

        $prefix = '+' . $dialingCode;

        return Str::startsWith($phone, $prefix) ? substr($phone, strlen($prefix)) : $phone;
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

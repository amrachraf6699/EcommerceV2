<?php

namespace Tests\Unit;

use App\Support\StorefrontCountryCatalog;
use Tests\TestCase;

class StorefrontCountryCatalogTest extends TestCase
{
    public function test_every_storefront_country_has_a_dialing_code(): void
    {
        $catalog = app(StorefrontCountryCatalog::class);

        $this->assertSame($catalog->countries(), array_keys($catalog->countryDialingCodes()));
        $this->assertCount(196, $catalog->countries());
        $this->assertSame('966', $catalog->dialingCodeForCountry('Saudi Arabia'));
        $this->assertSame('1', $catalog->dialingCodeForCountry('United States'));
    }
}

<?php

namespace Tests\Unit;

use App\Services\CurrencyDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CurrencyDetectionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_it_detects_country_and_currency_from_ipapi(): void
    {
        Http::fake([
            'http://ip-api.com/json/8.8.8.8?fields=status,countryCode' => Http::response([
                'status' => 'success',
                'countryCode' => 'US',
            ], 200),
        ]);

        $request = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '8.8.8.8']);
        $detected = app(CurrencyDetectionService::class)->detect($request);

        $this->assertSame('US', $detected['country_code']);
        $this->assertSame('USD', $detected['currency']);
        Http::assertSentCount(1);
    }

    public function test_it_skips_private_or_reserved_ips(): void
    {
        Http::fake([
            'http://ip-api.com/json/196.157.6.186?fields=status,countryCode' => Http::response([
                'status' => 'success',
                'countryCode' => 'EG',
            ], 200),
        ]);

        $request = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '127.0.0.1']);
        $detected = app(CurrencyDetectionService::class)->detect($request);

        $this->assertSame('EG', $detected['country_code']);
        $this->assertSame('EGP', $detected['currency']);
        Http::assertSentCount(1);
    }

    public function test_it_falls_back_to_next_free_provider_when_primary_is_rate_limited(): void
    {
        Http::fake([
            'http://ip-api.com/json/196.157.6.186?fields=status,countryCode' => Http::response([], 429),
            'https://ipwho.is/196.157.6.186' => Http::response([
                'success' => true,
                'country_code' => 'US',
            ], 200),
        ]);

        $request = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '196.157.6.186']);
        $detected = app(CurrencyDetectionService::class)->detect($request);

        $this->assertSame('US', $detected['country_code']);
        $this->assertSame('USD', $detected['currency']);
        Http::assertSentCount(2);
    }
}

<?php

namespace Tests\Unit;

use App\Services\CurrencyRateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CurrencyRateServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_it_fetches_a_bhd_exchange_rate(): void
    {
        Http::fake([
            'https://api.frankfurter.dev/*' => Http::response([
                'base' => 'BHD',
                'quote' => 'USD',
                'date' => '2026-04-29',
                'rate' => 2.65,
            ], 200),
        ]);

        $rate = app(CurrencyRateService::class)->rate('BHD', 'USD');

        $this->assertSame(2.65, $rate['rate']);
        $this->assertSame('2026-04-29', $rate['date']);
    }

    public function test_it_reuses_cached_rates(): void
    {
        Http::fake([
            'https://api.frankfurter.dev/*' => Http::response([
                'base' => 'BHD',
                'quote' => 'USD',
                'date' => '2026-04-29',
                'rate' => 2.65,
            ], 200),
        ]);

        $service = app(CurrencyRateService::class);

        $first = $service->rate('BHD', 'USD');
        $second = $service->rate('BHD', 'USD');

        $this->assertSame($first, $second);
        Http::assertSentCount(1);
    }

    public function test_it_falls_back_when_rate_lookup_fails(): void
    {
        Http::fake([
            'https://api.frankfurter.dev/*' => Http::response([], 500),
        ]);

        $rate = app(CurrencyRateService::class)->rate('BHD', 'USD');

        $this->assertNull($rate['rate']);
        $this->assertNull($rate['date']);
    }
}

<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use RuntimeException;

class TapPaymentService
{
    private const SETTINGS_GROUP = 'payment';

    public function __construct(private readonly HttpFactory $http)
    {
    }

    public function isConfigured(): bool
    {
        return $this->secretKey() !== '' && $this->publicKey() !== '';
    }

    /**
     * @param  array<string, mixed>  $customer
     * @return array<string, mixed>
     */
    public function createHostedCharge(Order $order, array $customer, string $redirectUrl, string $callbackUrl): array
    {
        $response = $this->client()->post('/charges', [
            'amount' => (float) $order->grand_total,
            'currency' => $order->currency,
            'threeDSecure' => true,
            'save_card' => false,
            'description' => 'Storefront order '.$order->order_number,
            'statement_descriptor' => config('app.name'),
            'metadata' => [
                'order_id' => (string) $order->id,
                'order_number' => $order->order_number,
                'session_id' => (string) $order->session_id,
            ],
            'reference' => [
                'transaction' => $order->order_number,
                'order' => $order->order_number,
            ],
            'receipt' => [
                'email' => false,
                'sms' => false,
            ],
            'customer' => array_filter([
                'first_name' => (string) Arr::get($customer, 'first_name', ''),
                'last_name' => (string) Arr::get($customer, 'last_name', ''),
                'email' => (string) Arr::get($customer, 'email', ''),
            ]),
            'source' => [
                'id' => 'src_all',
            ],
            'redirect' => [
                'url' => $redirectUrl,
            ],
            'post' => [
                'url' => $callbackUrl,
            ],
        ]);

        $data = $response->throw()->json();
        $redirect = data_get($data, 'transaction.url');

        if (! is_string($redirect) || $redirect === '') {
            throw new RuntimeException('Tap did not return a hosted checkout URL.');
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchCharge(string $tapChargeId): array
    {
        return $this->client()
            ->get('/charges/'.$tapChargeId)
            ->throw()
            ->json();
    }

    private function client()
    {
        $secretKey = $this->secretKey();
        $baseUrl = rtrim((string) Config::get('services.tap.base_url'), '/');

        if (! $this->isConfigured() || $baseUrl === '') {
            throw new RuntimeException('Tap payment credentials are not configured.');
        }

        return $this->http
            ->baseUrl($baseUrl.'/v2')
            ->acceptJson()
            ->asJson()
            ->withToken($secretKey);
    }

    private function secretKey(): string
    {
        return trim((string) Setting::query()
            ->where('group', self::SETTINGS_GROUP)
            ->where('key', 'tap_secret_key')
            ->value('value'));
    }

    private function publicKey(): string
    {
        return trim((string) Setting::query()
            ->where('group', self::SETTINGS_GROUP)
            ->where('key', 'tap_public_key')
            ->value('value'));
    }
}

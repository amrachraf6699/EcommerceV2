<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AfsPaymentService
{
    private const SETTINGS_GROUP = 'payment';

    public function __construct(private readonly HttpFactory $http)
    {
    }

    public function isConfigured(): bool
    {
        $config = $this->activeConfiguration();

        return $config['entity_id'] !== ''
            && $config['access_token'] !== ''
            && $config['base_url'] !== ''
            && $this->brands() !== [];
    }

    public function environment(): string
    {
        return $this->setting('afs_environment') === 'live' ? 'live' : 'sandbox';
    }

    /** @return list<string> */
    public function brands(): array
    {
        return collect(preg_split('/\s+/', strtoupper($this->setting('afs_brands')), -1, PREG_SPLIT_NO_EMPTY))
            ->filter(fn (string $brand): bool => (bool) preg_match('/^[A-Z0-9_]+$/', $brand))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function createCheckout(Order $order): array
    {
        Log::channel('payment')->info('Creating AFS checkout for order.', ['order' => $order]);

        $response = $this->client()->asForm()->post('/v1/checkouts', array_filter([
            'entityId' => $this->activeConfiguration()['entity_id'],
            'amount' => number_format((float) $order->grand_total, 2, '.', ''),
            'currency' => $order->currency,
            'paymentType' => 'DB',
            'merchantTransactionId' => $order->order_number,
            'customer.email' => $order->customer_email,
            'customer.givenName' => $order->customer_first_name,
            'customer.surname' => $order->customer_last_name,
        ], fn ($value): bool => $value !== null && $value !== ''));

        $checkout = $response->throw()->json();
        $checkoutId = (string) data_get($checkout, 'id');

        if ($checkoutId === '') {
            throw new RuntimeException('AFS did not return a checkout ID.');
        }

        return $checkout;
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchPaymentStatus(string $checkoutId, string $resourcePath): array
    {
        if (! $this->isExpectedResourcePath($checkoutId, $resourcePath)) {
            throw new RuntimeException('Invalid AFS payment status resource path.');
        }

        $response = $this->client()->get($resourcePath, [
            'entityId' => $this->activeConfiguration()['entity_id'],
        ]);

        Log::channel('payment')->info('AFS payment status response.', [
            'checkout_id' => $checkoutId,
            'resource_path' => $resourcePath,
            'http_status' => $response->status(),
            'response' => $response->json() ?? $response->body(),
        ]);

        return $response->throw()->json();
    }

    public function widgetUrl(string $checkoutId): string
    {
        return rtrim($this->activeConfiguration()['base_url'], '/')
            .'/v1/paymentWidgets.js?checkoutId='.rawurlencode($checkoutId);
    }

    public function isSuccessful(array $payment): bool
    {
        return (bool) preg_match('/^(000\.000\.|000\.100\.1|000\.[36])/', (string) data_get($payment, 'result.code'));
    }

    public function isPending(array $payment): bool
    {
        return (bool) preg_match('/^(000\.200|800\.400\.5)/', (string) data_get($payment, 'result.code'));
    }

    public function isValidPaymentResourcePath(string $checkoutId, string $resourcePath): bool
    {
        return $this->isExpectedResourcePath($checkoutId, $resourcePath);
    }

    /** @return array{entity_id:string,access_token:string,base_url:string} */
    private function activeConfiguration(): array
    {
        $environment = $this->environment();
        $baseUrl = $this->setting('afs_'.$environment.'_base_url');
        $accessToken = preg_replace('/^Bearer\s+/i', '', $this->setting('afs_'.$environment.'_access_token'));

        return [
            'entity_id' => $this->setting('afs_'.$environment.'_entity_id'),
            'access_token' => trim((string) $accessToken),
            'base_url' => $baseUrl !== ''
                ? $baseUrl
                : rtrim((string) config('services.afs.'.$environment.'_base_url'), '/'),
        ];
    }

    private function client(): PendingRequest
    {
        $config = $this->activeConfiguration();

        if (! $this->isConfigured()) {
            throw new RuntimeException('AFS payment credentials or payment brands are not configured.');
        }

        return $this->http
            ->baseUrl(rtrim($config['base_url'], '/'))
            ->acceptJson()
            ->withToken($config['access_token']);
    }

    private function isExpectedResourcePath(string $checkoutId, string $resourcePath): bool
    {
        return (bool) preg_match(
            '#^/v1/checkouts/'.preg_quote($checkoutId, '#').'/payment$#',
            $resourcePath,
        );
    }

    private function setting(string $key): string
    {
        return trim((string) Setting::query()
            ->where('group', self::SETTINGS_GROUP)
            ->where('key', $key)
            ->value('value'));
    }
}

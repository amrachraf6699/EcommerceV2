<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreCheckoutRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Services\TapPaymentService;
use App\Support\FrontendCheckoutManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly FrontendCheckoutManager $checkoutManager,
        private readonly TapPaymentService $tapPaymentService,
    ) {
    }

    public function summary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'coupon_code' => ['nullable', 'string', 'max:255'],
            'shipping_box_type' => ['nullable', 'string', 'in:with_box,without_box'],
        ]);

        return response()->json([
            'summary' => $this->checkoutManager->checkoutSummaryForCustomer($request->user(), $validated),
        ]);
    }

    public function store(StoreCheckoutRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $paymentMode = $request->string('payment_mode', 'native_sdk')->toString();
        $checkout = $this->checkoutManager->beginTapCheckoutForCustomer($request->user(), $validated, $paymentMode);

        return response()->json([
            'message' => 'Checkout initialized successfully.',
            'order' => new OrderResource($checkout['order']),
            'payment' => [
                'payment_provider' => 'tap',
                'payment_mode' => $paymentMode,
                'tap_public_key' => $checkout['tap_public_key'],
                'tap_charge_id' => data_get($checkout['hosted_charge'], 'id'),
                'hosted_redirect_url' => $checkout['hosted_redirect_url'],
                'hosted_charge' => $checkout['hosted_charge'],
                'native_sdk' => [
                    'provider' => 'tap',
                    'integration_type' => 'charge',
                    'charge_id' => data_get($checkout['hosted_charge'], 'id'),
                    'customer' => [
                        'first_name' => $validated['first_name'],
                        'last_name' => $validated['last_name'],
                        'email' => $checkout['order']->customer_email,
                    ],
                ],
            ],
        ], 201);
    }

    public function paymentStatus(Request $request, string $orderNumber): JsonResponse
    {
        $order = $request->user()->orders()
            ->with('items')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if ($order->payment_transaction_id) {
            $order = $this->checkoutManager->syncOrderFromTapCharge(
                $this->tapPaymentService->fetchCharge((string) $order->payment_transaction_id)
            );
        }

        return response()->json([
            'order' => new OrderResource($order->fresh('items')),
        ]);
    }

    public function tapCallback(Request $request): JsonResponse
    {
        $tapId = (string) ($request->input('id') ?: $request->input('tap_id'));
        abort_unless($tapId !== '', 404);

        $order = $this->checkoutManager->syncOrderFromTapCharge(
            $this->tapPaymentService->fetchCharge($tapId)
        );

        return response()->json([
            'status' => 'ok',
            'order_number' => $order->order_number,
        ]);
    }
}

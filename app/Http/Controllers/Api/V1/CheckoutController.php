<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreCheckoutRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Support\FrontendCheckoutManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly FrontendCheckoutManager $checkoutManager,
    ) {
    }

    public function summary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'coupon_code' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json([
            'summary' => $this->checkoutManager->checkoutSummaryForCustomer($request->user(), $validated),
        ]);
    }

    public function store(StoreCheckoutRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $checkout = $this->checkoutManager->beginAfsCheckoutForCustomer($request->user(), $validated);

        return response()->json([
            'message' => 'Checkout initialized successfully.',
            'order' => new OrderResource($checkout['order']),
            'payment' => [
                'payment_provider' => 'afs',
                'payment_mode' => 'hosted_widget',
                'checkout_id' => $checkout['checkout_id'],
                'hosted_payment_url' => $checkout['payment_url'],
                'payment_widget_url' => $checkout['widget_url'],
                'payment_brands' => $checkout['brands'],
            ],
        ], 201);
    }

    public function paymentStatus(Request $request, string $orderNumber): JsonResponse
    {
        $order = $request->user()->orders()
            ->with('items')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return response()->json([
            'order' => new OrderResource($order->fresh('items')),
        ]);
    }
}

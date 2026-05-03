<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreCheckoutRequest;
use App\Support\StorefrontCountryCatalog;
use App\Support\FrontendCheckoutManager;
use App\Services\TapPaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly FrontendCheckoutManager $checkoutManager,
        private readonly TapPaymentService $tapPaymentService,
        private readonly StorefrontCountryCatalog $countryCatalog,
    ) {
    }

    public function show(Request $request): View
    {
        if (! $request->session()->isStarted()) {
            $request->session()->start();
        }

        $cart = $this->checkoutManager->cartForSession($request->session()->getId());
        $checkoutForm = $this->checkoutManager->checkoutFormData($request);

        return view('frontend.checkout.show', [
            'cart' => $cart,
            'checkoutForm' => $checkoutForm,
            'checkoutSummary' => $this->checkoutManager->checkoutSummary(
                $request,
                $checkoutForm['country'] ?? null,
                $checkoutForm['email'] ?? null,
                $checkoutForm['coupon_code'] ?? null,
            ),
            'detectedCountryNameMap' => $this->countryCatalog->detectedCountryNameMap(),
            'tapCheckoutAvailable' => $this->tapPaymentService->isConfigured(),
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        if (! $request->session()->isStarted()) {
            $request->session()->start();
        }

        $summary = $this->checkoutManager->checkoutSummary(
            $request,
            $request->string('country')->toString() ?: null,
            $request->string('email')->toString() ?: null,
            $request->string('coupon_code')->toString() ?: null,
        );

        return response()->json([
            'summary' => $summary,
        ]);
    }

    public function store(StoreCheckoutRequest $request): RedirectResponse
    {
        if (! $this->tapPaymentService->isConfigured()) {
            throw ValidationException::withMessages([
                'cart' => __('storefront.checkout_maintenance'),
            ]);
        }

        $checkout = $this->checkoutManager->beginTapCheckout($request, $request->validated());

        return redirect()->away($checkout['redirect_url']);
    }

    public function result(Request $request): View
    {
        $order = null;

        if ($request->filled('tap_id')) {
            $order = $this->checkoutManager->syncOrderFromTapCharge(
                $this->tapPaymentService->fetchCharge((string) $request->string('tap_id')),
                $request,
            );
        } else {
            $order = $this->checkoutManager->findOrderForResult($request);
        }

        abort_unless($order, 404);

        return view('frontend.checkout.result', [
            'order' => $order,
        ]);
    }

    public function tapCallback(Request $request): JsonResponse
    {
        $tapId = (string) ($request->input('id') ?: $request->input('tap_id'));

        abort_unless($tapId !== '', 404);

        $order = $this->checkoutManager->syncOrderFromTapCharge(
            $this->tapPaymentService->fetchCharge($tapId),
        );

        return response()->json([
            'status' => 'ok',
            'order_number' => $order->order_number,
        ]);
    }

    public function tapCancel(Request $request): RedirectResponse
    {
        $order = $this->checkoutManager->findOrderForResult($request);
        abort_unless($order, 404);

        $this->checkoutManager->cancelOrder($order);

        return redirect()->route('storefront.checkout.result', [
            'locale' => app()->getLocale(),
            'order' => $order->order_number,
        ]);
    }
}

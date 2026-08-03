<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreCheckoutRequest;
use App\Models\Order;
use App\Support\StorefrontCountryCatalog;
use App\Support\FrontendCheckoutManager;
use App\Services\AfsPaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly FrontendCheckoutManager $checkoutManager,
        private readonly AfsPaymentService $afsPaymentService,
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
            'afsCheckoutAvailable' => $this->afsPaymentService->isConfigured(),
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
        if (! $this->afsPaymentService->isConfigured()) {
            throw ValidationException::withMessages([
                'cart' => __('storefront.checkout_maintenance'),
            ]);
        }

        $checkout = $this->checkoutManager->beginAfsCheckout($request, $request->validated());

        return redirect()->to($checkout['payment_url']);
    }

    public function payment(string $locale, string $order): View
    {
        $order = Order::query()
            ->where('order_number', $order)
            ->where('payment_provider', 'afs')
            ->firstOrFail();

        abort_unless($order->payment_reference && $this->afsPaymentService->isConfigured(), 404);

        return view('frontend.checkout.payment', [
            'order' => $order,
            'widgetUrl' => $this->afsPaymentService->widgetUrl($order->payment_reference),
            'widgetIntegrity' => null,
            'brands' => $this->afsPaymentService->brands(),
        ]);
    }

    public function result(Request $request): View|RedirectResponse
    {
        $order = $this->checkoutManager->findOrderForResult($request);
        abort_unless($order, 404);

        if ($request->filled('resourcePath') && $order->payment_provider === 'afs') {
            try {
                abort_unless($this->afsPaymentService->isValidPaymentResourcePath(
                    (string) $order->payment_reference,
                    (string) $request->string('resourcePath'),
                ), 404);

                $payment = $this->afsPaymentService->fetchPaymentStatus(
                    (string) $order->payment_reference,
                    (string) $request->string('resourcePath'),
                );
                $order = $this->checkoutManager->syncOrderFromAfsPayment($order, $payment, $request);
            } catch (RequestException|RuntimeException|ValidationException $exception) {
                Log::channel('payment')->warning('AFS payment verification failed for storefront result.', [
                    'order_number' => $order->order_number,
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ]);

                return redirect()->route('storefront.checkout.result', [
                    'locale' => app()->getLocale(),
                    'order' => $order->order_number,
                ])->with('payment_error', __('storefront.checkout_payment_verification_failed'));
            }
        }

        return view('frontend.checkout.result', [
            'order' => $order,
        ]);
    }
}

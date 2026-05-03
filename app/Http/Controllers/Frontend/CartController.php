<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Support\FrontendCartManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly FrontendCartManager $cartManager)
    {
    }

    public function show(Request $request): View
    {
        if (! $request->session()->isStarted()) {
            $request->session()->start();
        }

        return view('frontend.cart.show', [
            'cart' => $this->cartManager->cartForSession($request->session()->getId()),
        ]);
    }

    public function storeItem(Request $request, string $locale): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $summary = $this->cartManager->addItem(
            $request,
            (int) $validated['product_id'],
            (int) $validated['product_variant_id'],
            (int) $validated['quantity'],
        );

        return response()->json([
            'message' => __('storefront.product.added_to_cart'),
            'cart' => $summary,
        ]);
    }

    public function summary(Request $request, string $locale): JsonResponse
    {
        if (! $request->session()->isStarted()) {
            $request->session()->start();
        }

        return response()->json([
            'cart' => $this->cartManager->summaryForSession($request->session()->getId()),
        ]);
    }

    public function updateItem(Request $request, string $locale, int $item): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $summary = $this->cartManager->updateItem($request, $item, (int) $validated['quantity']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('storefront.cart_updated'),
                'cart' => $summary,
            ]);
        }

        return redirect()
            ->route('storefront.cart.show', ['locale' => app()->getLocale()])
            ->with('success', __('storefront.cart_updated'));
    }

    public function destroyItem(Request $request, string $locale, int $item): RedirectResponse|JsonResponse
    {
        $summary = $this->cartManager->removeItem($request, $item);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('storefront.cart_item_removed'),
                'cart' => $summary,
            ]);
        }

        return redirect()
            ->route('storefront.cart.show', ['locale' => app()->getLocale()])
            ->with('success', __('storefront.cart_item_removed'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductVariantRequest;
use App\Http\Requests\Admin\UpdateProductVariantRequest;
use App\Jobs\SendProductRestockReminders;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProductVariantController extends Controller
{
    public function store(StoreProductVariantRequest $request, Product $product): RedirectResponse|JsonResponse
    {
        DB::transaction(function () use ($request, $product): void {
            if ($request->boolean('is_default')) {
                $product->variants()->update(['is_default' => false]);
            }

            $variant = $product->variants()->create([
                ...$request->validated(),
                'sku' => ProductVariant::generateSku(),
                'is_default' => $request->boolean('is_default'),
                'is_active' => $request->boolean('is_active', true),
            ]);

            $this->ensureSingleDefaultVariant($product, $variant->id);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'تمت إضافة النسخة بنجاح.',
                'fragments' => ProductController::editorFragments($product->fresh()),
            ]);
        }

        return back()->with('success', 'تمت إضافة النسخة.');
    }

    public function update(UpdateProductVariantRequest $request, Product $product, ProductVariant $variant): RedirectResponse|JsonResponse
    {
        abort_unless($variant->product_id === $product->id, 404);

        $previousStock = (int) $variant->stock_quantity;

        DB::transaction(function () use ($request, $product, $variant): void {
            if ($request->boolean('is_default')) {
                $product->variants()->whereKeyNot($variant->id)->update(['is_default' => false]);
            }

            $variant->update([
                ...$request->validated(),
                'is_default' => $request->boolean('is_default'),
                'is_active' => $request->boolean('is_active'),
            ]);

            $this->ensureSingleDefaultVariant(
                $product,
                $request->boolean('is_default') ? $variant->id : null,
            );
        });

        if ($previousStock === 0 && (int) $variant->fresh()->stock_quantity > 0) {
            SendProductRestockReminders::dispatch($variant->id);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'تم تحديث النسخة بنجاح.',
                'fragments' => ProductController::editorFragments($product->fresh()),
            ]);
        }

        return back()->with('success', 'تم تحديث النسخة.');
    }

    public function destroy(Product $product, ProductVariant $variant): RedirectResponse|JsonResponse
    {
        abort_unless($variant->product_id === $product->id, 404);

        DB::transaction(function () use ($product, $variant): void {
            $variant->images()->update(['product_variant_id' => null]);
            $variant->delete();
            $this->ensureSingleDefaultVariant($product);
        });

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'تم حذف النسخة بنجاح.',
                'fragments' => ProductController::editorFragments($product->fresh()),
            ]);
        }

        return back()->with('success', 'تم حذف النسخة.');
    }

    private function ensureSingleDefaultVariant(Product $product, ?int $preferredVariantId = null): void
    {
        $variants = $product->variants()->orderBy('id')->get();

        if ($variants->isEmpty()) {
            return;
        }

        $defaultVariantId = $preferredVariantId && $variants->contains('id', $preferredVariantId)
            ? $preferredVariantId
            : $variants->firstWhere('is_default', true)?->id;

        $defaultVariantId ??= $variants->first()->id;

        $product->variants()->whereKeyNot($defaultVariantId)->update(['is_default' => false]);
        $product->variants()->whereKey($defaultVariantId)->update(['is_default' => true]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductImageRequest;
use App\Http\Requests\Admin\UpdateProductImageRequest;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function store(StoreProductImageRequest $request, Product $product): RedirectResponse|JsonResponse
    {
        DB::transaction(function () use ($request, $product): void {
            $images = $request->file('images', []);
            $baseSortOrder = (int) $request->input('sort_order', 0);
            $variantId = $request->validated('product_variant_id');
            $shouldUseFirstAsPrimary = $request->boolean('is_primary') || ! $product->images()->where('is_primary', true)->exists();

            if ($shouldUseFirstAsPrimary) {
                $product->images()->update(['is_primary' => false]);
            }

            $firstCreatedImageId = null;

            foreach ($images as $index => $image) {
                $createdImage = $product->images()->create([
                    'path' => $image->store('products', 'public'),
                    'alt_text' => $request->input('alt_text'),
                    'sort_order' => $baseSortOrder + $index,
                    'product_variant_id' => $variantId,
                    'is_primary' => $shouldUseFirstAsPrimary && $index === 0,
                ]);

                $firstCreatedImageId ??= $createdImage->id;
            }

            $this->ensureSinglePrimaryImage($product, $firstCreatedImageId);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'تم رفع الصور بنجاح.',
                'fragments' => ProductController::editorFragments($product->fresh()),
            ]);
        }

        return back()->with('success', 'تم رفع الصور.');
    }

    public function update(UpdateProductImageRequest $request, Product $product, ProductImage $image): RedirectResponse|JsonResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        DB::transaction(function () use ($request, $product, $image): void {
            if ($request->boolean('is_primary')) {
                $product->images()->whereKeyNot($image->id)->update(['is_primary' => false]);
            }

            $image->update([
                ...$request->validated(),
                'sort_order' => (int) $request->input('sort_order', 0),
                'is_primary' => $request->boolean('is_primary'),
            ]);

            $this->ensureSinglePrimaryImage(
                $product,
                $request->boolean('is_primary') ? $image->id : null,
            );
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'تم تحديث الصورة بنجاح.',
                'fragments' => ProductController::editorFragments($product->fresh()),
            ]);
        }

        return back()->with('success', 'تم تحديث الصورة.');
    }

    public function destroy(Product $product, ProductImage $image): RedirectResponse|JsonResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        DB::transaction(function () use ($product, $image): void {
            Storage::disk('public')->delete($image->path);
            $image->delete();
            $this->ensureSinglePrimaryImage($product);
        });

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'تم حذف الصورة بنجاح.',
                'fragments' => ProductController::editorFragments($product->fresh()),
            ]);
        }

        return back()->with('success', 'تم حذف الصورة.');
    }

    private function ensureSinglePrimaryImage(Product $product, ?int $preferredImageId = null): void
    {
        $images = $product->images()->orderBy('sort_order')->orderBy('id')->get();

        if ($images->isEmpty()) {
            return;
        }

        $primaryImageId = $preferredImageId && $images->contains('id', $preferredImageId)
            ? $preferredImageId
            : $images->firstWhere('is_primary', true)?->id;

        $primaryImageId ??= $images->first()->id;

        $product->images()->whereKeyNot($primaryImageId)->update(['is_primary' => false]);
        $product->images()->whereKey($primaryImageId)->update(['is_primary' => true]);
    }
}

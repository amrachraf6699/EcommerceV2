<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\LocalizedQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()
            ->with(['categories', 'variants'])
            ->withCount('variants')
            ->orderByDesc('is_featured')
            ->latest();

        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $query->where(fn ($builder) => $builder
                ->whereRaw(LocalizedQuery::expression('name', 'ar', false) . ' LIKE ?', ["%{$search}%"])
                ->orWhereRaw(LocalizedQuery::expression('name', 'en', false) . ' LIKE ?', ["%{$search}%"])
                ->orWhere('slug', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status')->toString() === 'active');
        }

        if ($request->filled('featured')) {
            $query->where('is_featured', $request->string('featured')->toString() === 'yes');
        }

        if ($request->filled('category')) {
            $categoryId = (int) $request->input('category');
            $query->whereHas('categories', fn ($builder) => $builder->whereKey($categoryId));
        }

        return view('admin.products.index', [
            'products' => $query->paginate(12)->withQueryString(),
            'categories' => Category::query()->where('is_active', true)->orderByRaw(LocalizedQuery::expression('name', 'ar', false))->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => self::activeCategories(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated): void {
            $product = Product::create([
                ...Arr::only($validated, [
                    'name',
                    'slug',
                    'short_description',
                    'description',
                    'notes',
                    'meta_title',
                    'meta_description',
                ]),
                'is_active' => $request->boolean('is_active', true),
                'is_featured' => $request->boolean('is_featured'),
            ]);

            $product->categories()->sync($validated['categories'] ?? []);

            $variantIdsByIndex = [];
            $defaultVariantSelected = false;

            foreach (($validated['variants'] ?? []) as $index => $variantData) {
                if (
                    blank($variantData['name'] ?? null)
                    && blank($variantData['price'] ?? null)
                    && blank($variantData['stock_quantity'] ?? null)
                ) {
                    continue;
                }

                $isDefault = ! $defaultVariantSelected && (bool) ($variantData['is_default'] ?? false);
                $defaultVariantSelected = $defaultVariantSelected || $isDefault;

                $variant = $product->variants()->create([
                    'name' => $variantData['name'] ?? null,
                    'sku' => ProductVariant::generateSku(),
                    'price' => $variantData['price'],
                    'compare_at_price' => $variantData['compare_at_price'] ?? null,
                    'stock_quantity' => $variantData['stock_quantity'],
                    'is_default' => $isDefault,
                    'is_active' => (bool) ($variantData['is_active'] ?? true),
                ]);

                $variantIdsByIndex[(int) $index] = $variant->id;
            }

            if (! empty($variantIdsByIndex) && ! $product->variants()->where('is_default', true)->exists()) {
                $product->variants()->whereKey(reset($variantIdsByIndex))->update(['is_default' => true]);
            }

            $images = $request->file('images', []);
            $baseSortOrder = (int) ($validated['image_sort_order'] ?? 0);
            $imageVariantIndex = $validated['image_variant_index'] ?? null;
            $imageVariantId = is_numeric($imageVariantIndex) ? ($variantIdsByIndex[(int) $imageVariantIndex] ?? null) : null;
            $shouldUseFirstAsPrimary = $request->boolean('images_primary') || ! $product->images()->where('is_primary', true)->exists();

            if ($shouldUseFirstAsPrimary) {
                $product->images()->update(['is_primary' => false]);
            }

            foreach ($images as $index => $image) {
                $product->images()->create([
                    'path' => $image->store('products', 'public'),
                    'alt_text' => $validated['image_alt_text'] ?? null,
                    'sort_order' => $baseSortOrder + $index,
                    'product_variant_id' => $imageVariantId,
                    'is_primary' => $shouldUseFirstAsPrimary && $index === 0,
                ]);
            }
        });

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'ØªÙ… Ø¥Ù†Ø´Ø§Ø¡ Ø§Ù„Ù…Ù†ØªØ¬.');
    }

    public function edit(Product $product): View
    {
        $product->load(['categories', 'variants', 'images.variant']);

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => self::activeCategories(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        $product->update([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
        ]);

        $product->categories()->sync($validated['categories'] ?? []);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Product details updated successfully.',
                'fragments' => self::editorFragments($product),
            ]);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'ØªÙ… ØªØ­Ø¯ÙŠØ« Ø¨ÙŠØ§Ù†Ø§Øª Ø§Ù„Ù…Ù†ØªØ¬.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        DB::transaction(function () use ($product): void {
            foreach ($product->images()->get() as $image) {
                Storage::disk('public')->delete($image->path);
            }

            $product->images()->delete();
            $product->variants()->delete();
            $product->delete();
        });

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'ØªÙ… Ø­Ø°Ù Ø§Ù„Ù…Ù†ØªØ¬.');
    }

    public static function editorFragments(Product $product): array
    {
        $product->loadMissing(['variants', 'images.variant']);
        $categories = self::activeCategories();

        return [
            'basic' => view('admin.products.partials.basic-panel', [
                'product' => $product,
                'categories' => $categories,
            ])->render(),
            'seo' => view('admin.products.partials.seo-panel', [
                'product' => $product,
            ])->render(),
            'variants' => view('admin.products.partials.variants-panel', [
                'product' => $product,
            ])->render(),
            'images' => view('admin.products.partials.images-panel', [
                'product' => $product,
            ])->render(),
        ];
    }

    private static function activeCategories()
    {
        return Category::query()
            ->where('is_active', true)
            ->orderByRaw(LocalizedQuery::expression('name', 'ar', false))
            ->get();
    }
}

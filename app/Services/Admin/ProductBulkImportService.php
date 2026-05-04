<?php

namespace App\Services\Admin;

use App\Exports\ProductImportTemplateProductsSheet;
use App\Imports\ProductBulkImportSheet;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Throwable;

class ProductBulkImportService
{
    private const ERROR_CACHE_TTL_SECONDS = 7200;

    private const MAX_ROWS = 500;

    public function import(UploadedFile $file, string $mode): array
    {
        $sheets = Excel::toCollection(new ProductBulkImportSheet(), $file);
        $rows = $sheets->first() instanceof Collection
            ? $sheets->first()
            : collect($sheets->first() ?? []);

        if ($rows->count() > self::MAX_ROWS) {
            throw ValidationException::withMessages([
                'file' => 'ملف الاستيراد كبير جدًا. الحد الأقصى هو 500 صف في كل عملية.',
            ]);
        }

        $summary = [
            'total_rows' => 0,
            'imported_count' => 0,
            'updated_count' => 0,
            'failed_count' => 0,
            'error_report_token' => null,
        ];

        $errors = [];

        foreach ($rows->values() as $index => $rawRow) {
            $row = $this->normalizeRow($rawRow instanceof Collection ? $rawRow : collect((array) $rawRow));

            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $summary['total_rows']++;

            try {
                $result = $this->importRow($row, $mode);
                $summary[$result === 'created' ? 'imported_count' : 'updated_count']++;
            } catch (Throwable $exception) {
                $summary['failed_count']++;
                $errors[] = [
                    (string) ($index + 2),
                    (string) ($this->stringValue($row->get('slug')) ?? ''),
                    $exception->getMessage(),
                ];
            }
        }

        if ($errors !== []) {
            $token = (string) Str::uuid();
            Cache::put($this->errorCacheKey($token), $errors, self::ERROR_CACHE_TTL_SECONDS);
            $summary['error_report_token'] = $token;
        }

        return $summary;
    }

    public function getErrors(string $token): ?array
    {
        $errors = Cache::get($this->errorCacheKey($token));

        return is_array($errors) ? $errors : null;
    }

    private function importRow(Collection $row, string $mode): string
    {
        $slug = $this->requiredString($row, 'slug', 'slug');
        $categories = $this->resolveCategories($row);
        $variants = $this->parseVariants($row);
        $productPayload = $this->buildProductPayload($row, $slug);
        $imageUrls = $this->parseDelimitedStrings($row->get('image_urls'));
        $imageAltText = $this->stringValue($row->get('image_alt_text'));
        $storedPaths = [];

        try {
            return DB::transaction(function () use (
                $slug,
                $mode,
                $categories,
                $variants,
                $productPayload,
                $imageUrls,
                $imageAltText,
                &$storedPaths
            ): string {
                $existingProduct = Product::withTrashed()
                    ->with(['variants' => fn ($query) => $query->withTrashed()])
                    ->where('slug', $slug)
                    ->first();

                if ($mode === 'create-only' && $existingProduct !== null) {
                    throw new RuntimeException('يوجد منتج بنفس الـ slug بالفعل.');
                }

                if ($existingProduct?->trashed()) {
                    throw new RuntimeException('المنتج المرتبط بهذا الـ slug محذوف حاليًا ولا يمكن الاستيراد عليه.');
                }

                $isUpdate = $existingProduct !== null && ! $existingProduct->trashed();
                $product = $existingProduct ?? Product::create($productPayload);

                if ($isUpdate) {
                    $product->update($productPayload);
                }

                if ($categories->isNotEmpty()) {
                    $product->categories()->sync($categories->pluck('id')->all());
                }

                $preparedVariants = $this->prepareVariantsForPersistence($product, $variants, $isUpdate);
                $defaultVariantId = $this->persistVariants($product, $preparedVariants);

                $this->importImages($product, $imageUrls, $imageAltText, $storedPaths);

                if (! $product->variants()->where('is_default', true)->exists()) {
                    $product->variants()->whereKey($defaultVariantId)->update(['is_default' => true]);
                }

                return $isUpdate ? 'updated' : 'created';
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }
    }

    private function buildProductPayload(Collection $row, string $slug): array
    {
        return [
            'slug' => $slug,
            'name' => [
                'ar' => $this->requiredString($row, 'name_ar', 'name_ar'),
                'en' => $this->requiredString($row, 'name_en', 'name_en'),
            ],
            'short_description' => $this->translatableField($row, 'short_description'),
            'description' => $this->translatableField($row, 'description'),
            'notes' => $this->translatableField($row, 'notes'),
            'meta_title' => $this->translatableField($row, 'meta_title'),
            'meta_description' => $this->translatableField($row, 'meta_description'),
            'is_active' => $this->booleanValue($row->get('is_active'), 'is_active', true),
            'is_featured' => $this->booleanValue($row->get('is_featured'), 'is_featured', false),
        ];
    }

    private function resolveCategories(Collection $row): Collection
    {
        $slugs = $this->parseDelimitedStrings($row->get('category_slugs'));

        if ($slugs === []) {
            return collect();
        }

        $categories = Category::query()->whereIn('slug', $slugs)->get()->keyBy('slug');
        $missingSlugs = array_values(array_diff($slugs, $categories->keys()->all()));

        if ($missingSlugs !== []) {
            throw new RuntimeException('هذه الأقسام غير موجودة: ' . implode(', ', $missingSlugs));
        }

        return collect($slugs)
            ->map(fn (string $slug) => $categories->get($slug))
            ->filter();
    }

    private function parseVariants(Collection $row): array
    {
        $names = $this->parseDelimitedStrings($row->get('variant_names'));
        $prices = $this->parseAlignedOptionalValues($row->get('variant_prices'));
        $stocks = $this->parseAlignedOptionalValues($row->get('variant_stocks'));

        if ($names === [] || $prices === [] || $stocks === []) {
            throw new RuntimeException('بيانات النسخ مطلوبة: variant_names و variant_prices و variant_stocks.');
        }

        $variantCount = count($names);

        if (count($prices) !== $variantCount || count($stocks) !== $variantCount) {
            throw new RuntimeException('عدد عناصر النسخ غير متطابق بين الأسماء والأسعار والمخزون.');
        }

        $skus = $this->parseOptionalColumn($row->get('variant_skus'), $variantCount, null);
        $comparePrices = $this->parseOptionalColumn($row->get('variant_compare_prices'), $variantCount, null);
        $defaults = $this->parseOptionalColumn($row->get('variant_is_default'), $variantCount, false);
        $activeFlags = $this->parseOptionalColumn($row->get('variant_is_active'), $variantCount, true);

        $variants = [];
        $defaultAssigned = false;

        for ($index = 0; $index < $variantCount; $index++) {
            $isDefault = $this->booleanValue($defaults[$index], 'variant_is_default', false);

            if ($isDefault && ! $defaultAssigned) {
                $defaultAssigned = true;
            } else {
                $isDefault = false;
            }

            $variants[] = [
                'name' => $names[$index],
                'sku' => blank($skus[$index]) ? null : $this->stringValue($skus[$index]),
                'price' => $this->decimalValue($prices[$index], 'variant_prices'),
                'compare_at_price' => blank($comparePrices[$index])
                    ? null
                    : $this->decimalValue($comparePrices[$index], 'variant_compare_prices'),
                'stock_quantity' => $this->integerValue($stocks[$index], 'variant_stocks'),
                'is_default' => $isDefault,
                'is_active' => $this->booleanValue($activeFlags[$index], 'variant_is_active', true),
            ];
        }

        if (! $defaultAssigned && $variants !== []) {
            $variants[0]['is_default'] = true;
        }

        return $variants;
    }

    private function prepareVariantsForPersistence(Product $product, array $variants, bool $isUpdate): array
    {
        if (! $isUpdate) {
            return array_map(function (array $variant): array {
                if ($variant['sku'] !== null && ProductVariant::withTrashed()->where('sku', $variant['sku'])->exists()) {
                    throw new RuntimeException("الـ SKU [{$variant['sku']}] مستخدم بالفعل.");
                }

                $variant['sku'] ??= ProductVariant::generateSku();

                return $variant;
            }, $variants);
        }

        $hasIncomingSku = collect($variants)->contains(fn (array $variant) => filled($variant['sku']));
        $currentVariants = $product->variants()->withTrashed()->get();

        if (! $hasIncomingSku) {
            if (count($variants) > 1 || $currentVariants->count() > 1) {
                throw new RuntimeException('الاستيراد بنمط upsert لعدة نسخ يتطلب تعبئة عمود variant_skus.');
            }

            return array_map(function (array $variant) use ($currentVariants): array {
                $existingVariant = $currentVariants->first();
                $variant['existing_variant_id'] = $existingVariant?->id;
                $variant['sku'] ??= $existingVariant?->sku ?? ProductVariant::generateSku();

                return $variant;
            }, $variants);
        }

        $existingBySku = $currentVariants->keyBy('sku');

        return array_map(function (array $variant) use ($product, $existingBySku): array {
            $sku = $variant['sku'];

            if ($sku === null) {
                throw new RuntimeException('عند استخدام upsert مع أكثر من نسخة، يجب تعبئة كل قيم variant_skus.');
            }

            $foreignVariant = ProductVariant::withTrashed()
                ->where('sku', $sku)
                ->where('product_id', '!=', $product->id)
                ->first();

            if ($foreignVariant !== null) {
                throw new RuntimeException("الـ SKU [{$sku}] مرتبط بمنتج آخر.");
            }

            $variant['existing_variant_id'] = $existingBySku->get($sku)?->id;

            return $variant;
        }, $variants);
    }

    private function persistVariants(Product $product, array $variants): int
    {
        $product->variants()->update(['is_default' => false]);
        $defaultVariantId = null;

        foreach ($variants as $variantData) {
            $existingVariantId = $variantData['existing_variant_id'] ?? null;
            unset($variantData['existing_variant_id']);

            $variant = $existingVariantId
                ? $product->variants()->withTrashed()->findOrFail($existingVariantId)
                : null;

            if ($variant?->trashed()) {
                $variant->restore();
            }

            if ($variant !== null) {
                $variant->update($variantData);
            } else {
                $variant = $product->variants()->create($variantData);
            }

            if ($variant->is_default) {
                $defaultVariantId = $variant->id;
            }
        }

        return $defaultVariantId ?? (int) $product->variants()->value('id');
    }

    private function importImages(Product $product, array $imageUrls, ?string $altText, array &$storedPaths): void
    {
        if ($imageUrls === []) {
            return;
        }

        $baseSortOrder = ((int) $product->images()->max('sort_order')) + 1;
        $hasPrimary = $product->images()->where('is_primary', true)->exists();
        $importedCount = 0;
        $errors = [];

        foreach ($imageUrls as $index => $url) {
            try {
                $response = Http::timeout(20)->retry(1, 200)->get($url)->throw();
                $extension = $this->detectExtension($url, $response->header('Content-Type'));
                $path = 'products/' . Str::uuid() . '.' . $extension;

                Storage::disk('public')->put($path, $response->body());
                $storedPaths[] = $path;

                $product->images()->create([
                    'path' => $path,
                    'alt_text' => $altText,
                    'sort_order' => $baseSortOrder + $index,
                    'product_variant_id' => null,
                    'is_primary' => ! $hasPrimary && $importedCount === 0,
                ]);

                $importedCount++;
            } catch (Throwable $exception) {
                $errors[] = "فشل تنزيل الصورة [{$url}]";
            }
        }

        if ($importedCount === 0 && $errors !== []) {
            throw new RuntimeException(implode(' - ', $errors));
        }
    }

    private function detectExtension(string $url, ?string $contentType): string
    {
        $extension = strtolower((string) pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

        if ($extension !== '') {
            return $extension;
        }

        $normalizedType = strtolower(Str::before((string) $contentType, ';'));

        return match ($normalizedType) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };
    }

    private function translatableField(Collection $row, string $base): ?array
    {
        $arabic = $this->stringValue($row->get($base . '_ar'));
        $english = $this->stringValue($row->get($base . '_en'));

        if ($arabic === null && $english === null) {
            return null;
        }

        return [
            'ar' => $arabic,
            'en' => $english,
        ];
    }

    private function parseOptionalColumn(mixed $value, int $expectedCount, mixed $default): array
    {
        $values = $this->parseAlignedOptionalValues($value);

        if ($values === []) {
            return array_fill(0, $expectedCount, $default);
        }

        if (count($values) !== $expectedCount) {
            throw new RuntimeException('عدد القيم في أعمدة النسخ غير متطابق.');
        }

        return $values;
    }

    private function parseAlignedOptionalValues(mixed $value): array
    {
        if (blank($value)) {
            return [];
        }

        return array_map(
            static fn (string $part): string => trim($part),
            explode('|', (string) $value)
        );
    }

    private function parseDelimitedStrings(mixed $value): array
    {
        return array_values(array_filter(
            array_map(
                fn (string $part): ?string => $this->stringValue($part),
                explode('|', (string) $value)
            ),
            static fn (?string $part): bool => $part !== null
        ));
    }

    private function requiredString(Collection $row, string $key, string $label): string
    {
        $value = $this->stringValue($row->get($key));

        if ($value === null) {
            throw new RuntimeException("الحقل {$label} مطلوب.");
        }

        return $value;
    }

    private function stringValue(mixed $value): ?string
    {
        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function decimalValue(mixed $value, string $label): string
    {
        if (! is_numeric($value)) {
            throw new RuntimeException("قيمة {$label} يجب أن تكون رقمية.");
        }

        $numeric = (float) $value;

        if ($numeric < 0) {
            throw new RuntimeException("قيمة {$label} يجب أن تكون أكبر من أو تساوي صفر.");
        }

        return number_format($numeric, 2, '.', '');
    }

    private function integerValue(mixed $value, string $label): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new RuntimeException("قيمة {$label} يجب أن تكون عددًا صحيحًا.");
        }

        $numeric = (int) $value;

        if ($numeric < 0) {
            throw new RuntimeException("قيمة {$label} يجب أن تكون أكبر من أو تساوي صفر.");
        }

        return $numeric;
    }

    private function booleanValue(mixed $value, string $label, ?bool $default = null): ?bool
    {
        if (blank($value)) {
            return $default;
        }

        $normalized = strtolower(trim((string) $value));

        return match ($normalized) {
            '1', 'true', 'yes', 'on' => true,
            '0', 'false', 'no', 'off' => false,
            default => throw new RuntimeException("قيمة {$label} غير صالحة. استخدم 1/0 أو true/false أو yes/no."),
        };
    }

    private function rowIsEmpty(Collection $row): bool
    {
        foreach (ProductImportTemplateProductsSheet::COLUMN_KEYS as $heading) {
            if ($this->stringValue($row->get($heading)) !== null) {
                return false;
            }
        }

        return true;
    }

    private function normalizeRow(Collection $row): Collection
    {
        $normalized = collect();

        foreach ($row as $key => $value) {
            $mappedKey = ProductImportTemplateProductsSheet::HEADING_MAP[(string) $key] ?? null;

            if ($mappedKey === null) {
                continue;
            }

            $normalized->put($mappedKey, $value);
        }

        return $normalized;
    }

    private function errorCacheKey(string $token): string
    {
        return 'product-import-errors:' . $token;
    }
}

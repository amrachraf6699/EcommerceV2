<?php

namespace Tests\Feature;

use App\Exports\ProductImportErrorsExport;
use App\Exports\ProductImportTemplateExport;
use App\Exports\ProductImportTemplateProductsSheet;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\AdminAuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class AdminProductBulkImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        $this->seed(AdminAuthorizationSeeder::class);

        $this->superAdmin = User::factory()->create(['is_active' => true]);
        $this->superAdmin->assignRole('super-admin');
    }

    public function test_admin_can_download_product_import_template(): void
    {
        Excel::fake();

        $this->actingAs($this->superAdmin)
            ->get(route('admin.products.import.template'))
            ->assertOk();

        Excel::assertDownloaded('products-import-template.xlsx', function (ProductImportTemplateExport $export): bool {
            return count($export->sheets()) === 2;
        });
    }

    public function test_create_only_import_creates_product_variants_categories_and_images(): void
    {
        Storage::fake('public');
        Http::fake([
            'https://example.com/shoe-front.jpg' => Http::response('front-image', 200, ['Content-Type' => 'image/jpeg']),
            'https://example.com/shoe-side.jpg' => Http::response('side-image', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        Category::create(['name' => 'Shoes', 'slug' => 'shoes']);
        Category::create(['name' => 'Featured', 'slug' => 'featured']);

        $file = $this->makeImportFile([
            [
                'slug' => 'sport-shoe-1',
                'name_ar' => 'حذاء رياضي',
                'name_en' => 'Sport Shoe',
                'short_description_ar' => 'وصف قصير',
                'short_description_en' => 'Short description',
                'description_ar' => 'تفاصيل المنتج',
                'description_en' => 'Product details',
                'notes_ar' => '',
                'notes_en' => '',
                'meta_title_ar' => 'عنوان',
                'meta_title_en' => 'Title',
                'meta_description_ar' => 'وصف',
                'meta_description_en' => 'Description',
                'is_active' => '1',
                'is_featured' => '1',
                'category_slugs' => 'shoes|featured',
                'variant_names' => '42|43',
                'variant_skus' => '',
                'variant_prices' => '1200|1250',
                'variant_compare_prices' => '1300|1350',
                'variant_stocks' => '5|7',
                'variant_is_default' => '1|0',
                'variant_is_active' => '1|1',
                'image_urls' => 'https://example.com/shoe-front.jpg|https://example.com/shoe-side.jpg',
                'image_alt_text' => 'Sport shoe',
            ],
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('admin.products.import.store'), [
            'file' => $file,
            'mode' => 'create-only',
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('product_import_summary');

        $product = Product::where('slug', 'sport-shoe-1')->firstOrFail()->load('categories', 'variants', 'images');

        $this->assertSame('حذاء رياضي', $product->getTranslation('name', 'ar'));
        $this->assertCount(2, $product->categories);
        $this->assertCount(2, $product->variants);
        $this->assertCount(2, $product->images);
        $this->assertSame(1, $product->images()->where('is_primary', true)->count());
        $this->assertTrue($product->variants()->where('is_default', true)->exists());

        foreach ($product->images as $image) {
            Storage::disk('public')->assertExists($image->path);
        }
    }

    public function test_create_only_import_reports_row_errors_and_can_download_error_export(): void
    {
        Category::create(['name' => 'Shoes', 'slug' => 'shoes']);
        Product::create([
            'name' => ['ar' => 'قديم', 'en' => 'Old'],
            'slug' => 'duplicate-product',
        ]);

        $file = $this->makeImportFile([
            [
                'slug' => 'duplicate-product',
                'name_ar' => 'مكرر',
                'name_en' => 'Duplicate',
                'is_active' => '1',
                'is_featured' => '0',
                'category_slugs' => 'shoes',
                'variant_names' => 'Only',
                'variant_prices' => '100',
                'variant_stocks' => '3',
            ],
            [
                'slug' => 'bad-category-product',
                'name_ar' => 'قسم خاطئ',
                'name_en' => 'Bad Category',
                'is_active' => '1',
                'is_featured' => '0',
                'category_slugs' => 'missing-category',
                'variant_names' => 'Only',
                'variant_prices' => '100',
                'variant_stocks' => '3',
            ],
            [
                'slug' => 'mismatch-product',
                'name_ar' => 'قيم غير متطابقة',
                'name_en' => 'Mismatch',
                'is_active' => '1',
                'is_featured' => '0',
                'category_slugs' => 'shoes',
                'variant_names' => 'A|B',
                'variant_prices' => '100',
                'variant_stocks' => '4|5',
            ],
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('admin.products.import.store'), [
            'file' => $file,
            'mode' => 'create-only',
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('product_import_summary');

        $summary = session('product_import_summary');

        $this->assertSame(3, $summary['failed_count']);
        $this->assertNotEmpty($summary['error_report_token']);
        $this->assertDatabaseCount('products', 1);

        Excel::fake();

        $this->actingAs($this->superAdmin)
            ->get(route('admin.products.import.errors', $summary['error_report_token']))
            ->assertOk();

        Excel::assertDownloaded('product-import-errors.xlsx', function (ProductImportErrorsExport $export): bool {
            return true;
        });
    }

    public function test_upsert_import_updates_existing_product_variant_and_appends_images(): void
    {
        Storage::fake('public');
        Http::fake([
            'https://example.com/new-image.jpg' => Http::response('new-image', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $category = Category::create(['name' => 'Shoes', 'slug' => 'shoes']);
        $product = Product::create([
            'name' => ['ar' => 'قديم', 'en' => 'Old'],
            'slug' => 'upsert-product',
            'is_active' => true,
        ]);
        $product->categories()->sync([$category->id]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => '42',
            'sku' => 'SHOE-42',
            'price' => 1000,
            'stock_quantity' => 2,
            'is_default' => true,
            'is_active' => true,
        ]);

        $existingImage = ProductImage::create([
            'product_id' => $product->id,
            'product_variant_id' => null,
            'path' => UploadedFile::fake()->image('existing.png')->store('products', 'public'),
            'alt_text' => 'Old image',
            'sort_order' => 0,
            'is_primary' => true,
        ]);

        $file = $this->makeImportFile([
            [
                'slug' => 'upsert-product',
                'name_ar' => 'محدث',
                'name_en' => 'Updated',
                'is_active' => '1',
                'is_featured' => '1',
                'category_slugs' => 'shoes',
                'variant_names' => '42',
                'variant_skus' => 'SHOE-42',
                'variant_prices' => '1500',
                'variant_compare_prices' => '1700',
                'variant_stocks' => '9',
                'variant_is_default' => '1',
                'variant_is_active' => '1',
                'image_urls' => 'https://example.com/new-image.jpg',
                'image_alt_text' => 'Updated shoe',
            ],
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('admin.products.import.store'), [
            'file' => $file,
            'mode' => 'upsert',
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $variant->refresh();

        $this->assertSame('محدث', $product->getTranslation('name', 'ar'));
        $this->assertTrue($product->is_featured);
        $this->assertSame('1500.00', $variant->price);
        $this->assertSame(9, $variant->stock_quantity);
        $this->assertCount(2, $product->images);
        $this->assertDatabaseHas('product_images', ['id' => $existingImage->id, 'is_primary' => true]);
    }

    public function test_upsert_without_variant_skus_fails_for_ambiguous_multi_variant_rows(): void
    {
        $category = Category::create(['name' => 'Shoes', 'slug' => 'shoes']);
        $product = Product::create([
            'name' => ['ar' => 'منتج', 'en' => 'Product'],
            'slug' => 'ambiguous-product',
        ]);
        $product->categories()->sync([$category->id]);

        ProductVariant::create([
            'product_id' => $product->id,
            'name' => '42',
            'sku' => 'AMB-42',
            'price' => 100,
            'stock_quantity' => 1,
            'is_default' => true,
            'is_active' => true,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'name' => '43',
            'sku' => 'AMB-43',
            'price' => 110,
            'stock_quantity' => 2,
            'is_default' => false,
            'is_active' => true,
        ]);

        $file = $this->makeImportFile([
            [
                'slug' => 'ambiguous-product',
                'name_ar' => 'منتج محدث',
                'name_en' => 'Updated Product',
                'is_active' => '1',
                'is_featured' => '0',
                'category_slugs' => 'shoes',
                'variant_names' => '42|43',
                'variant_prices' => '120|130',
                'variant_stocks' => '3|4',
            ],
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('admin.products.import.store'), [
            'file' => $file,
            'mode' => 'upsert',
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('product_import_summary');

        $summary = session('product_import_summary');

        $this->assertSame(1, $summary['failed_count']);
        $this->assertSame(0, $summary['updated_count']);
        $this->assertSame('منتج', $product->fresh()->getTranslation('name', 'ar'));
    }

    private function makeImportFile(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headings = ProductImportTemplateProductsSheet::HEADINGS;
        $columnKeys = ProductImportTemplateProductsSheet::COLUMN_KEYS;

        $sheet->fromArray($headings, null, 'A1');

        foreach (array_values($rows) as $index => $row) {
            $sheet->fromArray(
                array_map(
                    static fn (string $columnKey): mixed => $row[$columnKey] ?? '',
                    $columnKeys
                ),
                null,
                'A' . ($index + 2)
            );
        }

        $path = tempnam(sys_get_temp_dir(), 'products-import-');
        $xlsxPath = $path . '.xlsx';
        rename($path, $xlsxPath);

        (new Xlsx($spreadsheet))->save($xlsxPath);

        return new UploadedFile(
            $xlsxPath,
            'products-import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }
}

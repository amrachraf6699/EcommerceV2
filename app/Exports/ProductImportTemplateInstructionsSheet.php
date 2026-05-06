<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductImportTemplateInstructionsSheet implements FromArray, ShouldAutoSize, WithTitle
{
    public function array(): array
    {
        return [
            ['Bulk Products Import Instructions'],
            ['Required columns', 'slug, name_ar, name_en, variant_sizes, variant_colors, variant_prices, variant_stocks'],
            ['Optional columns', 'category_slugs, short_description_*, description_*, notes_*, meta_title_*, meta_description_*, is_active, is_featured, variant_compare_prices, variant_is_default, variant_is_active, image_urls, image_alt_text'],
            ['Delimiter', 'Use pipe | for multi-value columns such as category_slugs, variant_sizes, variant_colors, variant_prices, variant_compare_prices, variant_stocks, variant_is_default, variant_is_active, image_urls'],
            ['Boolean values', 'Accepted values: 1, 0, true, false, yes, no, on, off'],
            ['Categories', 'Optional. Reference categories by slug when needed. Example: shoes|running'],
            ['Images', 'Use public downloadable image URLs. The importer stores them in storage/public/products. Separate multiple image URLs with |'],
            ['Variant rules', 'Variant sizes, colors, prices, and stocks must align by position. Upsert matches existing variants by the same size and color combination.'],
            ['Single-variant example', 'sport-shoe-1', 'حذاء رياضي', 'Sport Shoe', 'وصف قصير', 'Short copy', 'تفاصيل المنتج', 'Product details', '', '', 'عنوان SEO', 'SEO title', 'وصف SEO', 'SEO description', '1', '1', 'shoes|featured', 'EU 42', 'Black', '1200', '', '15', '1', '1', 'https://example.com/shoe-front.jpg|https://example.com/shoe-side.jpg', 'Sport shoe'],
            ['Multi-variant example', 'sport-shirt-1', 'تيشيرت رياضي', 'Sport Shirt', '', '', '', '', '', '', '', '', '', '', '1', '0', 'shirts', 'Small|Medium|Large', 'Black|Black|White', '650|650|700', '||', '5|7|4', '1|0|0', '1|1|1', 'https://example.com/shirt.jpg', 'Sport shirt'],
        ];
    }

    public function title(): string
    {
        return 'Instructions';
    }
}

<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductImportTemplateProductsSheet implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    public const COLUMN_KEYS = [
        'slug',
        'name_ar',
        'name_en',
        'short_description_ar',
        'short_description_en',
        'description_ar',
        'description_en',
        'notes_ar',
        'notes_en',
        'meta_title_ar',
        'meta_title_en',
        'meta_description_ar',
        'meta_description_en',
        'is_active',
        'is_featured',
        'category_slugs',
        'variant_names',
        'variant_skus',
        'variant_prices',
        'variant_compare_prices',
        'variant_stocks',
        'variant_is_default',
        'variant_is_active',
        'image_urls',
        'image_alt_text',
    ];

    public const HEADINGS = [
        'الرابط المختصر',
        'الاسم بالعربية',
        'الاسم بالإنجليزية',
        'الوصف المختصر بالعربية',
        'الوصف المختصر بالإنجليزية',
        'الوصف الكامل بالعربية',
        'الوصف الكامل بالإنجليزية',
        'ملاحظات بالعربية',
        'ملاحظات بالإنجليزية',
        'عنوان الميتا بالعربية',
        'عنوان الميتا بالإنجليزية',
        'وصف الميتا بالعربية',
        'وصف الميتا بالإنجليزية',
        'مفعل',
        'مميز',
        'أقسام المنتج',
        'أسماء النسخ',
        'أكواد SKU للنسخ',
        'أسعار النسخ',
        'أسعار المقارنة للنسخ',
        'مخزون النسخ',
        'النسخة الافتراضية',
        'حالة النسخ',
        'روابط الصور',
        'النص البديل للصور',
    ];

    public const HEADING_MAP = [
        'slug' => 'slug',
        'الرابط المختصر' => 'slug',
        'name_ar' => 'name_ar',
        'الاسم بالعربية' => 'name_ar',
        'name_en' => 'name_en',
        'الاسم بالإنجليزية' => 'name_en',
        'short_description_ar' => 'short_description_ar',
        'الوصف المختصر بالعربية' => 'short_description_ar',
        'short_description_en' => 'short_description_en',
        'الوصف المختصر بالإنجليزية' => 'short_description_en',
        'description_ar' => 'description_ar',
        'الوصف الكامل بالعربية' => 'description_ar',
        'description_en' => 'description_en',
        'الوصف الكامل بالإنجليزية' => 'description_en',
        'notes_ar' => 'notes_ar',
        'ملاحظات بالعربية' => 'notes_ar',
        'notes_en' => 'notes_en',
        'ملاحظات بالإنجليزية' => 'notes_en',
        'meta_title_ar' => 'meta_title_ar',
        'عنوان الميتا بالعربية' => 'meta_title_ar',
        'meta_title_en' => 'meta_title_en',
        'عنوان الميتا بالإنجليزية' => 'meta_title_en',
        'meta_description_ar' => 'meta_description_ar',
        'وصف الميتا بالعربية' => 'meta_description_ar',
        'meta_description_en' => 'meta_description_en',
        'وصف الميتا بالإنجليزية' => 'meta_description_en',
        'is_active' => 'is_active',
        'مفعل' => 'is_active',
        'is_featured' => 'is_featured',
        'مميز' => 'is_featured',
        'category_slugs' => 'category_slugs',
        'أقسام المنتج' => 'category_slugs',
        'variant_names' => 'variant_names',
        'أسماء النسخ' => 'variant_names',
        'variant_skus' => 'variant_skus',
        'أكواد SKU للنسخ' => 'variant_skus',
        'variant_prices' => 'variant_prices',
        'أسعار النسخ' => 'variant_prices',
        'variant_compare_prices' => 'variant_compare_prices',
        'أسعار المقارنة للنسخ' => 'variant_compare_prices',
        'variant_stocks' => 'variant_stocks',
        'مخزون النسخ' => 'variant_stocks',
        'variant_is_default' => 'variant_is_default',
        'النسخة الافتراضية' => 'variant_is_default',
        'variant_is_active' => 'variant_is_active',
        'حالة النسخ' => 'variant_is_active',
        'image_urls' => 'image_urls',
        'روابط الصور' => 'image_urls',
        'image_alt_text' => 'image_alt_text',
        'النص البديل للصور' => 'image_alt_text',
    ];

    public function array(): array
    {
        return [
            [
                'runner-pro-shoe',
                'حذاء رانر برو',
                'Runner Pro Shoe',
                'حذاء جري خفيف للتمارين اليومية',
                'Lightweight running shoe for daily training',
                'حذاء رياضي بوسادة مريحة وثبات ممتاز ومناسب للجري والتمارين.',
                'Athletic shoe with responsive cushioning, strong support, and everyday running comfort.',
                '',
                '',
                'حذاء رانر برو',
                'Runner Pro Shoe',
                'مثال مرجعي لمنتج يحتوي على أكثر من نسخة وصورتين.',
                'Reference product with multiple variants and two images.',
                '1',
                '1',
                'shoes|featured',
                'EU 42|EU 43',
                'RUNNER-PRO-42|RUNNER-PRO-43',
                '2499|2599',
                '2799|2899',
                '12|9',
                '1|0',
                '1|1',
                'https://example.com/products/runner-pro-front.jpg|https://example.com/products/runner-pro-side.jpg',
                'Runner Pro Shoe',
            ],
            [
                'training-tee-core',
                'تيشيرت تدريب كور',
                'Training Tee Core',
                'تيشيرت رياضي بتهوية جيدة',
                'Performance training tee',
                'تيشيرت مناسب للتمارين اليومية مصنوع من خامة خفيفة وسريعة الجفاف.',
                'Quick-dry training tee made for gym sessions and daily activewear.',
                '',
                '',
                'تيشيرت تدريب كور',
                'Training Tee Core',
                'مثال مرجعي لمنتج بثلاث نسخ ومقارنة سعر اختيارية.',
                'Reference product with three variants and optional compare prices.',
                '1',
                '0',
                'apparel|new-arrivals',
                'Small|Medium|Large',
                'TEE-CORE-S|TEE-CORE-M|TEE-CORE-L',
                '799|799|849',
                '899|899|949',
                '20|18|14',
                '1|0|0',
                '1|1|1',
                'https://example.com/products/training-tee-core.jpg',
                'Training Tee Core',
            ],
        ];
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function title(): string
    {
        return 'Products Import';
    }
}

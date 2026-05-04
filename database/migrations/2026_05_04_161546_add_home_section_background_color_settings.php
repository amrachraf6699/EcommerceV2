<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach ([
            [
                'key' => 'home_brands_section_background_color',
                'label' => 'لون خلفية قسم علاماتنا الرياضية',
                'value' => '#000000',
                'description' => 'اختر لون خلفية قسم علاماتنا الرياضية في الصفحة الرئيسية.',
                'sort_order' => 1001,
            ],
            [
                'key' => 'home_new_arrivals_section_background_color',
                'label' => 'لون خلفية قسم أحدث الإضافات',
                'value' => '#121212',
                'description' => 'اختر لون خلفية قسم أحدث الإضافات في الصفحة الرئيسية.',
                'sort_order' => 1002,
            ],
        ] as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'group' => 'appearance',
                    'label' => $setting['label'],
                    'value' => $setting['value'],
                    'input_type' => 'color',
                    'description' => $setting['description'],
                    'options' => null,
                    'is_public' => false,
                    'sort_order' => $setting['sort_order'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'home_brands_section_background_color',
            'home_new_arrivals_section_background_color',
        ])->delete();
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $legacyOneToTwo = DB::table('settings')
            ->where('group', 'shipping')
            ->where('key', 'shipping_europe_america_1_2_cost')
            ->value('value');

        $legacyThreePlus = DB::table('settings')
            ->where('group', 'shipping')
            ->where('key', 'shipping_europe_america_3_plus_cost')
            ->value('value');

        DB::table('settings')
            ->where('group', 'shipping')
            ->whereIn('key', [
                'shipping_type',
                'shipping_europe_america_1_2_cost',
                'shipping_europe_america_3_plus_cost',
            ])
            ->delete();

        $shippingOthersCost = $legacyOneToTwo ?? $legacyThreePlus ?? '0';

        DB::table('settings')->updateOrInsert(
            ['key' => 'shipping_others_cost'],
            [
                'group' => 'shipping',
                'label' => 'شحن باقي الدول',
                'value' => (string) $shippingOthersCost,
                'input_type' => 'number',
                'description' => 'قيمة الشحن لكل قطعة لباقي الدول خارج الخليج. ضع 0 للشحن المجاني.',
                'options' => null,
                'is_public' => false,
                'sort_order' => 40,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('shipping_with_box')->nullable()->after('shipping_postal_code');
            $table->string('shipping_zone')->nullable()->after('shipping_total');
            $table->string('shipping_rate_source')->nullable()->after('shipping_zone');
            $table->decimal('shipping_unit_cost', 12, 2)->nullable()->after('shipping_rate_source');
            $table->decimal('shipping_quantity_multiplier', 12, 2)->nullable()->after('shipping_unit_cost');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_with_box',
                'shipping_zone',
                'shipping_rate_source',
                'shipping_unit_cost',
                'shipping_quantity_multiplier',
            ]);
        });

        DB::table('settings')
            ->where('group', 'shipping')
            ->where('key', 'shipping_others_cost')
            ->delete();
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->boolean('has_box')->default(false)->after('stock_quantity');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('shipping_with_box');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('shipping_with_box')->nullable()->after('shipping_postal_code');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('has_box');
        });
    }
};

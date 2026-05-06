<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table): void {
            $table->string('size')->default('Default')->after('product_id');
            $table->string('color')->default('Default')->after('size');
        });

        DB::table('product_variants')
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->update([
                'size' => DB::raw('name'),
            ]);

        Schema::table('product_variants', function (Blueprint $table): void {
            $table->dropColumn(['name', 'sku', 'barcode']);
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table): void {
            $table->string('name')->nullable()->after('product_id');
            $table->string('sku')->nullable()->unique()->after('color');
            $table->string('barcode')->nullable()->unique()->after('sku');
        });

        DB::table('product_variants')->update([
            'name' => DB::raw("TRIM(CONCAT(size, ' - ', color))"),
        ]);

        Schema::table('product_variants', function (Blueprint $table): void {
            $table->dropColumn(['size', 'color']);
        });
    }
};

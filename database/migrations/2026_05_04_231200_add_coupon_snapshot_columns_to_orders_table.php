<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('coupon_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('coupons')
                ->nullOnDelete();
            $table->string('coupon_code')->nullable()->after('coupon_id');
            $table->string('coupon_type', 20)->nullable()->after('coupon_code');
            $table->decimal('coupon_value', 10, 2)->nullable()->after('coupon_type');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn([
                'coupon_code',
                'coupon_type',
                'coupon_value',
            ]);
        });
    }
};

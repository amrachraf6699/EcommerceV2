<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')
            ->whereNotIn('status', ['pending', 'processing', 'canceled'])
            ->update(['status' => 'pending']);

        DB::table('orders')
            ->whereNotIn('payment_status', ['unpaid', 'pending', 'paid', 'failed', 'canceled'])
            ->update(['payment_status' => 'unpaid']);

        DB::table('orders')
            ->whereNotIn('fulfillment_status', ['unfulfilled', 'shipped', 'delivered'])
            ->update(['fulfillment_status' => 'unfulfilled']);

        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','processing','canceled') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('unpaid','pending','paid','failed','canceled') NOT NULL DEFAULT 'unpaid'");
        DB::statement("ALTER TABLE orders MODIFY fulfillment_status ENUM('unfulfilled','shipped','delivered') NOT NULL DEFAULT 'unfulfilled'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY status VARCHAR(255) NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE orders MODIFY payment_status VARCHAR(255) NOT NULL DEFAULT 'unpaid'");
        DB::statement("ALTER TABLE orders MODIFY fulfillment_status VARCHAR(255) NOT NULL DEFAULT 'unfulfilled'");
    }
};

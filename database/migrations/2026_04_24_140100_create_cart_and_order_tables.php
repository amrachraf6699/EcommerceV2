<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->string('currency', 3)->default('USD');
            $table->unsignedInteger('item_count')->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->string('product_name');
            $table->string('variant_name')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();

            $table->unique(['cart_id', 'product_variant_id']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('session_id')->nullable()->index();
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->string('fulfillment_status')->default('unfulfilled');
            $table->string('currency', 3)->default('USD');
            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('billing_country')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_address_line_1')->nullable();
            $table->string('billing_address_line_2')->nullable();
            $table->string('billing_postal_code')->nullable();
            $table->boolean('shipping_same_as_billing')->default(true);
            $table->string('shipping_country')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_address_line_1')->nullable();
            $table->string('shipping_address_line_2')->nullable();
            $table->string('shipping_postal_code')->nullable();
            $table->text('customer_note')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('shipping_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('product_name');
            $table->string('variant_name')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};

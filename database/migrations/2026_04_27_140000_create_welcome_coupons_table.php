<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('welcome_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('email')->unique();
            $table->string('code')->unique();
            $table->string('discount_type', 20);
            $table->decimal('discount_value', 10, 2);
            $table->string('locale', 10)->default(config('app.locale', 'ar'));
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('welcome_coupons');
    }
};

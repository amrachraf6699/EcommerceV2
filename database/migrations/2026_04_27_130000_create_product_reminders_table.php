<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('locale', 10);
            $table->string('active_key')->nullable()->unique();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index(['product_variant_id', 'notified_at']);
            $table->index(['customer_id', 'notified_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reminders');
    }
};

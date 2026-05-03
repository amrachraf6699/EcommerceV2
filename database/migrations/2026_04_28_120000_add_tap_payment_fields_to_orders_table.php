<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_provider')->nullable()->after('payment_status');
            $table->string('payment_reference')->nullable()->after('payment_provider');
            $table->string('payment_transaction_id')->nullable()->after('payment_reference');
            $table->text('payment_redirect_url')->nullable()->after('payment_transaction_id');

            $table->index('payment_reference');
            $table->index('payment_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['payment_reference']);
            $table->dropIndex(['payment_transaction_id']);
            $table->dropColumn([
                'payment_provider',
                'payment_reference',
                'payment_transaction_id',
                'payment_redirect_url',
            ]);
        });
    }
};

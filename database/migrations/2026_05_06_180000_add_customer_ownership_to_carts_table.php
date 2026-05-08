<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->string('session_id')->nullable()->change();
            $table->foreignId('customer_id')
                ->nullable()
                ->after('session_id')
                ->constrained('customers')
                ->nullOnDelete();
            $table->unique('customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique(['customer_id']);
            $table->dropConstrainedForeignId('customer_id');
            $table->string('session_id')->nullable(false)->change();
        });
    }
};

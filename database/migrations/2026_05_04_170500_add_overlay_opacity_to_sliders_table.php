<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->decimal('overlay_opacity_start', 4, 2)->default(0.90)->after('button_text_color');
            $table->decimal('overlay_opacity_end', 4, 2)->default(0.55)->after('overlay_opacity_start');
        });
    }

    public function down(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->dropColumn(['overlay_opacity_start', 'overlay_opacity_end']);
        });
    }
};

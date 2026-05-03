<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('image');
            $table->string('link')->nullable();
            $table->string('text_color')->default('#111111');
            $table->string('button_background_color')->default('#111111');
            $table->string('button_text_color')->default('#ffffff');
            $table->enum('horizontal_align', ['left', 'center', 'right'])->default('center');
            $table->enum('vertical_align', ['top', 'center', 'bottom'])->default('center');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sliders') || ! Schema::hasColumn('sliders', 'link')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->rebuildSqliteTable();

            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE sliders MODIFY link VARCHAR(255) NULL');

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE sliders ALTER COLUMN link DROP NOT NULL');

            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement('ALTER TABLE sliders ALTER COLUMN link NVARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('sliders') || ! Schema::hasColumn('sliders', 'link')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::table('sliders')->whereNull('link')->update(['link' => '']);
            $this->rebuildSqliteTable(false);

            return;
        }

        if ($driver === 'mysql') {
            DB::table('sliders')->whereNull('link')->update(['link' => '']);
            DB::statement("ALTER TABLE sliders MODIFY link VARCHAR(255) NOT NULL DEFAULT ''");

            return;
        }

        if ($driver === 'pgsql') {
            DB::table('sliders')->whereNull('link')->update(['link' => '']);
            DB::statement('ALTER TABLE sliders ALTER COLUMN link SET NOT NULL');

            return;
        }

        if ($driver === 'sqlsrv') {
            DB::table('sliders')->whereNull('link')->update(['link' => '']);
            DB::statement("ALTER TABLE sliders ALTER COLUMN link NVARCHAR(255) NOT NULL DEFAULT ''");
        }
    }

    private function rebuildSqliteTable(bool $linkNullable = true): void
    {
        Schema::create('sliders_temp', function (Blueprint $table) use ($linkNullable) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('image');
            $linkColumn = $table->string('link');
            if ($linkNullable) {
                $linkColumn->nullable();
            }
            $table->string('text_color')->default('#111111');
            $table->string('button_background_color')->default('#111111');
            $table->string('button_text_color')->default('#ffffff');
            $table->enum('horizontal_align', ['left', 'center', 'right'])->default('center');
            $table->enum('vertical_align', ['top', 'center', 'bottom'])->default('center');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::statement('
            INSERT INTO sliders_temp (
                id, title, subtitle, image, link, text_color, button_background_color,
                button_text_color, horizontal_align, vertical_align, is_active, created_at, updated_at
            )
            SELECT
                id, title, subtitle, image, link, text_color, button_background_color,
                button_text_color, horizontal_align, vertical_align, is_active, created_at, updated_at
            FROM sliders
        ');

        Schema::drop('sliders');
        Schema::rename('sliders_temp', 'sliders');
    }
};

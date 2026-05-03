<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->migrateTableToJson(
            'products',
            [
                'name' => false,
                'short_description' => true,
                'description' => true,
                'notes' => true,
                'meta_title' => true,
                'meta_description' => true,
            ]
        );

        $this->migrateTableToJson(
            'categories',
            [
                'name' => false,
                'description' => true,
            ]
        );

        $this->migrateTableToJson(
            'pages',
            [
                'title' => false,
                'content' => true,
            ]
        );

        $this->migrateTableToJson(
            'sliders',
            [
                'title' => true,
                'subtitle' => true,
            ]
        );
    }

    public function down(): void
    {
        $this->migrateTableFromJson(
            'products',
            [
                'name' => false,
                'short_description' => true,
                'description' => true,
                'notes' => true,
                'meta_title' => true,
                'meta_description' => true,
            ]
        );

        $this->migrateTableFromJson(
            'categories',
            [
                'name' => false,
                'description' => true,
            ]
        );

        $this->migrateTableFromJson(
            'pages',
            [
                'title' => false,
                'content' => true,
            ]
        );

        $this->migrateTableFromJson(
            'sliders',
            [
                'title' => true,
                'subtitle' => true,
            ]
        );
    }

    /**
     * @param array<string, bool> $columns
     */
    private function migrateTableToJson(string $table, array $columns): void
    {
        $isSqlite = $this->usesSqlite();

        Schema::table($table, function (Blueprint $blueprint) use ($columns): void {
            foreach ($columns as $column => $nullable) {
                $jsonColumn = $column . '_i18n';
                $columnDefinition = $this->usesSqlite()
                    ? $blueprint->text($jsonColumn)
                    : $blueprint->json($jsonColumn);

                if ($nullable) {
                    $columnDefinition->nullable();
                }
            }
        });

        DB::table($table)->orderBy('id')->each(function (object $row) use ($table, $columns): void {
            $payload = [];

            foreach ($columns as $column => $nullable) {
                $value = $row->{$column};
                $payload[$column . '_i18n'] = $value === null && $nullable
                    ? null
                    : json_encode([
                        'ar' => $value ?? '',
                        'en' => '',
                    ], JSON_UNESCAPED_UNICODE);
            }

            DB::table($table)->where('id', $row->id)->update($payload);
        });

        Schema::table($table, function (Blueprint $blueprint) use ($columns): void {
            $blueprint->dropColumn(array_keys($columns));
        });

        foreach ($columns as $column => $nullable) {
            if ($isSqlite) {
                DB::statement("ALTER TABLE \"{$table}\" RENAME COLUMN \"{$column}_i18n\" TO \"{$column}\"");
                continue;
            }

            $nullSql = $nullable ? 'NULL' : 'NOT NULL';
            DB::statement("ALTER TABLE `{$table}` CHANGE `{$column}_i18n` `{$column}` JSON {$nullSql}");
        }
    }

    /**
     * @param array<string, bool> $columns
     */
    private function migrateTableFromJson(string $table, array $columns): void
    {
        $isSqlite = $this->usesSqlite();

        Schema::table($table, function (Blueprint $blueprint) use ($columns): void {
            foreach ($columns as $column => $nullable) {
                $textColumn = $column . '_text';
                $columnDefinition = $column === 'name' || $column === 'title' || $column === 'meta_title'
                    ? $blueprint->string($textColumn)
                    : ($column === 'description' || $column === 'content' ? $blueprint->longText($textColumn) : $blueprint->text($textColumn));

                if ($nullable) {
                    $columnDefinition->nullable();
                }
            }
        });

        DB::table($table)->orderBy('id')->each(function (object $row) use ($table, $columns): void {
            $payload = [];

            foreach ($columns as $column => $nullable) {
                $translations = json_decode((string) $row->{$column}, true);
                $arabicValue = $translations['ar'] ?? $translations['en'] ?? null;
                $payload[$column . '_text'] = $arabicValue === '' && $nullable ? null : $arabicValue;
            }

            DB::table($table)->where('id', $row->id)->update($payload);
        });

        Schema::table($table, function (Blueprint $blueprint) use ($columns): void {
            $blueprint->dropColumn(array_keys($columns));
        });

        foreach ($columns as $column => $nullable) {
            if ($isSqlite) {
                DB::statement("ALTER TABLE \"{$table}\" RENAME COLUMN \"{$column}_text\" TO \"{$column}\"");
                continue;
            }

            $typeSql = match ($column) {
                'name', 'title', 'meta_title' => 'VARCHAR(255)',
                'description', 'content' => 'LONGTEXT',
                default => 'TEXT',
            };
            $nullSql = $nullable ? 'NULL' : 'NOT NULL';

            DB::statement("ALTER TABLE `{$table}` CHANGE `{$column}_text` `{$column}` {$typeSql} {$nullSql}");
        }
    }

    private function usesSqlite(): bool
    {
        return Schema::getConnection()->getDriverName() === 'sqlite';
    }
};

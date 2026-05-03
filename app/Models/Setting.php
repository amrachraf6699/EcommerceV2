<?php

namespace App\Models;

use App\Support\SettingsManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'label',
        'value',
        'input_type',
        'description',
        'options',
        'is_public',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_public' => 'boolean',
    ];

    protected static function booted(): void
    {
        $flush = static function (): void {
            if (app()->bound(SettingsManager::class)) {
                app(SettingsManager::class)->forget();
            }
        };

        static::saved($flush);
        static::deleted($flush);
    }
}

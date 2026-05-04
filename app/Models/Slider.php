<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Slider extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'title',
        'subtitle',
    ];

    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'link',
        'text_color',
        'button_background_color',
        'button_text_color',
        'overlay_opacity_start',
        'overlay_opacity_end',
        'horizontal_align',
        'vertical_align',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'overlay_opacity_start' => 'decimal:2',
        'overlay_opacity_end' => 'decimal:2',
    ];
}

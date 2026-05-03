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
        'horizontal_align',
        'vertical_align',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

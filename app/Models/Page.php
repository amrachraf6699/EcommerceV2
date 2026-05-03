<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    public array $translatable = [
        'title',
        'content',
    ];

    protected $fillable = [
        'title',
        'slug',
        'content',
    ];
}

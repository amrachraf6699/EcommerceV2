<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class PlaceholderPageController extends Controller
{
    public function __invoke(string $title, string $description): View
    {
        return view('admin.placeholders.module', [
            'title' => $title,
            'description' => $description,
        ]);
    }
}

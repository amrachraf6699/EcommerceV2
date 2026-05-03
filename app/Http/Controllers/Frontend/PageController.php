<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function show(string $locale, Page $page): View
    {
        return view('frontend.pages.show', [
            'page' => $page,
        ]);
    }
}

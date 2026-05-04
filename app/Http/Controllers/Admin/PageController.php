<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePageRequest;
use App\Http\Requests\Admin\UpdatePageRequest;
use App\Models\Page;
use App\Support\LocalizedQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index(Request $request): View
    {
        $query = Page::query()->latest('updated_at');

        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $query->where(fn ($builder) => $builder
                ->whereRaw(LocalizedQuery::expression('title', 'ar', false) . ' LIKE ?', ["%{$search}%"])
                ->orWhereRaw(LocalizedQuery::expression('title', 'en', false) . ' LIKE ?', ["%{$search}%"])
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhereRaw(LocalizedQuery::expression('content', 'ar', false) . ' LIKE ?', ["%{$search}%"])
                ->orWhereRaw(LocalizedQuery::expression('content', 'en', false) . ' LIKE ?', ["%{$search}%"]));
        }

        return view('admin.pages.index', [
            'pages' => $query->paginate(12)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.create');
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        Page::create($request->validated());

        return redirect()
            ->route('admin.pages.index')
            ->with('success', '?? ????? ?????? ?????.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $page->update($request->validated());

        return redirect()
            ->route('admin.pages.index')
            ->with('success', '?? ????? ?????? ?????.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with('success', '?? ??? ?????? ?????.');
    }
}

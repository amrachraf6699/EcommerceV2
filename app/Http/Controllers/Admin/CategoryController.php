<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Support\LocalizedQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Category::query()->withCount('products')->orderBy('sort_order')->orderByRaw(LocalizedQuery::expression('name', 'ar', false));

        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $query->where(fn ($builder) => $builder
                ->whereRaw(LocalizedQuery::expression('name', 'ar', false) . ' LIKE ?', ["%{$search}%"])
                ->orWhereRaw(LocalizedQuery::expression('name', 'en', false) . ' LIKE ?', ["%{$search}%"])
                ->orWhere('slug', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status')->toString() === 'active');
        }

        return view('admin.categories.index', [
            'categories' => $query->paginate(12)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Category::create([
            ...Arr::except($validated, ['image', 'size_guide']),
            'image' => $request->hasFile('image') ? $request->file('image')->store('categories', 'public') : null,
            'size_guide' => $request->hasFile('size_guide') ? $request->file('size_guide')->store('categories/size-guides', 'public') : null,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'تم إنشاء القسم بنجاح.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $validated = $request->validated();
        $data = Arr::except($validated, ['image', 'size_guide']);

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        if ($request->hasFile('size_guide')) {
            if ($category->size_guide) {
                Storage::disk('public')->delete($category->size_guide);
            }

            $data['size_guide'] = $request->file('size_guide')->store('categories/size-guides', 'public');
        }

        $category->update([
            ...$data,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'تم تحديث القسم.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        if ($category->size_guide) {
            Storage::disk('public')->delete($category->size_guide);
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'تم حذف القسم.');
    }
}

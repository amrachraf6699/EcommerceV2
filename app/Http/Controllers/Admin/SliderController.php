<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSliderRequest;
use App\Http\Requests\Admin\UpdateSliderRequest;
use App\Models\Slider;
use App\Support\LocalizedQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Slider::query()->latest();

        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $query->where(fn ($builder) => $builder
                ->whereRaw(LocalizedQuery::expression('title', 'ar', false) . ' LIKE ?', ["%{$search}%"])
                ->orWhereRaw(LocalizedQuery::expression('title', 'en', false) . ' LIKE ?', ["%{$search}%"])
                ->orWhereRaw(LocalizedQuery::expression('subtitle', 'ar', false) . ' LIKE ?', ["%{$search}%"])
                ->orWhereRaw(LocalizedQuery::expression('subtitle', 'en', false) . ' LIKE ?', ["%{$search}%"])
                ->orWhere('link', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status')->toString() === 'active');
        }

        return view('admin.sliders.index', [
            'sliders' => $query->paginate(12)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.sliders.create', [
            'slider' => new Slider(),
        ]);
    }

    public function store(StoreSliderRequest $request): RedirectResponse
    {
        Slider::create([
            ...$request->safe()->except('image'),
            'image' => $request->file('image')->store('sliders', 'public'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.sliders.index')
            ->with('success', 'تم إنشاء الشريحة بنجاح.');
    }

    public function edit(Slider $slider): View
    {
        return view('admin.sliders.edit', compact('slider'));
    }

    public function update(UpdateSliderRequest $request, Slider $slider): RedirectResponse
    {
        $data = [
            ...$request->safe()->except('image'),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($slider->image);
            $data['image'] = $request->file('image')->store('sliders', 'public');
        }

        $slider->update($data);

        return redirect()
            ->route('admin.sliders.index')
            ->with('success', 'تم تحديث الشريحة.');
    }

    public function destroy(Slider $slider): RedirectResponse
    {
        Storage::disk('public')->delete($slider->image);
        $slider->delete();

        return redirect()
            ->route('admin.sliders.index')
            ->with('success', 'تم حذف الشريحة.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $settings = Setting::query()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');

        $currentGroup = $request->string('group')->toString();
        $currentGroup = $settings->has($currentGroup) ? $currentGroup : (string) $settings->keys()->first();

        return view('admin.settings.index', [
            'groups' => $settings,
            'currentGroup' => $currentGroup,
            'currentSettings' => $settings->get($currentGroup, collect()),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $group = $request->validated('group');
        $this->syncLegacyBrandInputs($request);

        $settings = Setting::query()->where('group', $group)->get();

        foreach ($settings as $setting) {
            if ($setting->input_type === 'file') {
                if ($request->hasFile($setting->key)) {
                    if ($setting->value) {
                        Storage::disk('public')->delete($setting->value);
                    }

                    $setting->value = $request->file($setting->key)->store('settings', 'public');
                    $setting->save();
                }

                continue;
            }

            if ($setting->input_type === 'boolean') {
                $setting->value = $request->boolean($setting->key) ? '1' : '0';
                $setting->save();

                continue;
            }

            if ($setting->input_type === 'password') {
                if ($request->filled($setting->key)) {
                    $setting->value = $request->input($setting->key);
                    $setting->save();
                }

                continue;
            }

            $setting->value = $request->input($setting->key);
            $setting->save();
        }

        $this->syncLegacyBrandSetting($request, $group);

        return redirect()
            ->route('admin.settings.index', ['group' => $group])
            ->with('success', 'تم حفظ الإعدادات.');
    }

    protected function syncLegacyBrandInputs(Request $request): void
    {
        if ($request->input('group') !== 'brand') {
            return;
        }

        if ($request->filled('address') && ! $request->filled('address_ar')) {
            $request->merge([
                'address_ar' => $request->input('address'),
                'address_en' => $request->input('address_en', $request->input('address')),
            ]);
        }

        if (! $request->has('address') && $request->filled('address_ar')) {
            $request->merge([
                'address' => $request->input('address_ar'),
            ]);
        }
    }

    protected function syncLegacyBrandSetting(Request $request, string $group): void
    {
        if ($group !== 'brand' || ! $request->filled('address')) {
            return;
        }

        Setting::query()->updateOrCreate(
            ['key' => 'address'],
            [
                'group' => 'brand',
                'label' => 'العنوان',
                'value' => $request->input('address'),
                'input_type' => 'textarea',
                'description' => null,
                'options' => null,
                'is_public' => false,
                'sort_order' => 999,
            ]
        );
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminPasswordRequest;
use App\Http\Requests\Admin\UpdateAdminProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.profile.edit');
    }

    public function update(UpdateAdminProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return back()->with('success', 'تم تحديث بيانات الحساب.');
    }

    public function updatePassword(UpdateAdminPasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->string('password')->toString(),
        ]);

        return back()->with('success', 'تم تحديث كلمة المرور.');
    }
}

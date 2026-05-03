<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreContactMessageRequest;
use App\Models\ContactMessage;
use App\Services\RecaptchaVerifier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ContactMessageController extends Controller
{
    public function show(RecaptchaVerifier $recaptchaVerifier): View
    {
        return view('frontend.contact.show', [
            'recaptchaSiteKey' => $recaptchaVerifier->siteKey(),
        ]);
    }

    public function store(StoreContactMessageRequest $request, RecaptchaVerifier $recaptchaVerifier): RedirectResponse
    {
        if (
            $recaptchaVerifier->isConfigured()
            && ! $recaptchaVerifier->verify($request->input('g-recaptcha-response'), $request->ip())
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'g-recaptcha-response' => __('storefront.contact.recaptcha_failed'),
                ]);
        }

        $validated = $request->validated();

        ContactMessage::query()->create([
            'name' => $validated['name'],
            'phone' => ($validated['phone'] ?? null) ?: null,
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ]);

        return redirect()
            ->route('storefront.contact.show')
            ->with('success', __('storefront.contact.sent'));
    }
}

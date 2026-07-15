<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReplyContactMessageRequest;
use App\Mail\ContactMessageReplyMail;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $query = ContactMessage::query()->latest();

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('subject', 'like', '%' . $search . '%')
                    ->orWhere('message', 'like', '%' . $search . '%');
            });
        }

        return view('admin.contact-messages.index', [
            'messages' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function show(ContactMessage $contactMessage): View
    {
        return view('admin.contact-messages.show', [
            'contactMessage' => $contactMessage,
        ]);
    }

    public function reply(ReplyContactMessageRequest $request, ContactMessage $contactMessage): RedirectResponse
    {
        $validated = $request->validated();

        Mail::to($contactMessage->email)->queue(new ContactMessageReplyMail(
            $contactMessage,
            $validated['subject'],
            $validated['message']
        ));

        $contactMessage->forceFill([
            'is_replied' => true,
        ])->save();

        return redirect()
            ->route('admin.contact-messages.index')
            ->with('success', 'تم إرسال الرد بنجاح.');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return redirect()
            ->route('admin.contact-messages.index')
            ->with('success', 'تم حذف الرسالة.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function markRead(DatabaseNotification $notification): RedirectResponse
    {
        abort_unless(
            $notification->notifiable_id === auth()->id()
            && $notification->notifiable_type === auth()->user()?->getMorphClass(),
            403
        );

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return redirect(data_get($notification->data, 'url', route('admin.dashboard')));
    }

    public function markAllRead(): RedirectResponse
    {
        auth()->user()?->unreadNotifications->markAsRead();

        return back()->with('success', 'تم تعليم جميع الإشعارات كمقروءة.');
    }
}

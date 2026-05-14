<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function markAsRead(Request $request, UserNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()?->id, 403);

        if (! $notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return back()->with('success', 'Bildirim okundu olarak işaretlendi.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()?->notifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('success', 'Tüm bildirimler okundu olarak işaretlendi.');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Notifikasi bell (desain Figma, dipakai sisi ibu hamil & bidan/kader).
 * Satu controller, dua guard — cuma satu yang aktif di tiap request lewat
 * middleware auth:pregnant / auth:staff pada masing-masing route.
 */
class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $notifiable = $this->notifiable();

        return response()->json([
            'notifications' => $notifiable->notifications()->latest()->limit(20)->get(),
            'unread_count' => $notifiable->unreadNotifications()->count(),
        ]);
    }

    public function markRead(string $notification): RedirectResponse
    {
        $this->notifiable()->notifications()->whereKey($notification)->first()?->markAsRead();

        return back();
    }

    public function markAllRead(): RedirectResponse
    {
        $this->notifiable()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }

    protected function notifiable()
    {
        return Auth::guard('pregnant')->user() ?? Auth::guard('staff')->user() ?? abort(403);
    }
}

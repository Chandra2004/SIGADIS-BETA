<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(): Response
    {
        $worker = Auth::guard('staff')->user();

        $notifications = $worker->notifications()
            ->latest()
            ->take(50)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'data' => $n->data,
                'read_at' => $n->read_at,
                'created_at' => $n->created_at->toISOString(),
            ]);

        return Inertia::render('Desktop/Notifikasi', [
            'notifications' => $notifications,
            'unreadCount' => $worker->unreadNotifications()->count(),
        ]);
    }

    public function markRead(string $id): RedirectResponse
    {
        $worker = Auth::guard('staff')->user();
        $notification = $worker->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return back()->with('success', 'Notifikasi ditandai dibaca.');
    }

    public function markAllRead(): RedirectResponse
    {
        $worker = Auth::guard('staff')->user();
        $worker->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}

<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'info' => fn () => $request->session()->get('info'),
            ],
            'auth' => [
                'user' => fn () => $request->user(),
                'admin' => fn () => Auth::guard('admin')->user()?->only('id', 'full_name', 'email', 'institution'),
                'staff' => fn () => Auth::guard('staff')->user()?->only('id', 'full_name', 'phone_number', 'role', 'status', 'str_number', 'appointment_letter_ref', 'region_code'),
                'pregnant' => fn () => Auth::guard('pregnant')->user()?->only('id', 'phone_number', 'full_name'),
            ],
            // Badge lonceng notifikasi (desain Figma) — dipakai sisi ibu hamil & bidan/kader.
            'unreadNotificationCount' => function () {
                $notifiable = Auth::guard('pregnant')->user() ?? Auth::guard('staff')->user();

                return $notifiable?->unreadNotifications()->count() ?? 0;
            },
            // Flows.md §29.2.2: preferensi ukuran teks berlaku di seluruh layar sejak saat itu.
            'textSize' => fn () => Auth::guard('pregnant')->user()?->text_size ?? 'normal',
            // Matriks Pembedaan Platform (Web vs Mobile/Capacitor)
            'isMobileApp' => fn () => (
                $request->header('X-Is-Native') === '1'
                || in_array($request->header('X-Capacitor-Platform'), ['android', 'ios'])
                || str_contains($request->userAgent() ?? '', 'Capacitor')
                || str_contains($request->userAgent() ?? '', 'wv') // Android WebView
            ),
            'platform' => fn () => $request->header('X-Capacitor-Platform', 'web'),
        ];
    }
}

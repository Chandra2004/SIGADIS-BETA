<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Registrasi device token (Architecture.md §5.4): dipanggil dari dashboard
 * desktop (browser push) atau app bidan/kader saat login, supaya push FCM
 * (SendPushForEmergencyAlert) punya token tujuan.
 */
class DeviceTokenController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fcm_token' => ['required', 'string'],
            'platform' => ['required', 'in:desktop_browser,mobile'],
        ]);

        $worker = Auth::guard('staff')->user();

        DeviceToken::updateOrCreate(
            ['healthcare_worker_id' => $worker->id, 'fcm_token' => $data['fcm_token']],
            ['platform' => $data['platform'], 'registered_at' => now(), 'last_seen_at' => now(), 'is_active' => true],
        );

        return back();
    }

    public function destroy(Request $request): RedirectResponse
    {
        $data = $request->validate(['fcm_token' => ['required', 'string']]);
        $worker = Auth::guard('staff')->user();

        DeviceToken::where('healthcare_worker_id', $worker->id)
            ->where('fcm_token', $data['fcm_token'])
            ->update(['is_active' => false]);

        return back();
    }
}

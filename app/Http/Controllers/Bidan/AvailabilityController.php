<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Inertia\Inertia;
use Inertia\Response;

/**
 * Flows.md §34.2: status nonaktif sementara (cuti/sakit), beda dari
 * verifikasi admin (§16.4.3) — murni ditandai sendiri oleh yang bersangkutan.
 */
class AvailabilityController extends Controller
{
    public function index(): Response
    {
        $worker = Auth::guard('staff')->user();

        return Inertia::render('Desktop/Cuti', [
            'worker' => [
                'id' => $worker->id,
                'full_name' => $worker->full_name,
                'role' => $worker->role,
                'region_code' => $worker->region_code,
                'is_available' => (bool) $worker->is_available,
                'unavailable_from' => $worker->unavailable_from,
                'unavailable_until' => $worker->unavailable_until,
            ],
        ]);
    }

    /** §34.2.2: menonaktifkan butuh konfirmasi eksplisit, boleh ada batas tanggal. */
    public function deactivate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'unavailable_from' => ['nullable', 'date'],
            'unavailable_until' => ['nullable', 'date'],
        ]);

        Auth::guard('staff')->user()->update([
            'is_available' => false,
            'unavailable_from' => $data['unavailable_from'] ?? now()->toDateString(),
            'unavailable_until' => $data['unavailable_until'] ?? null,
        ]);

        return back()->with('success', 'Status nonaktif sementara diaktifkan.');
    }

    /** §34.2.4: aktif kembali tidak perlu konfirmasi tambahan. */
    public function reactivate(): RedirectResponse
    {
        Auth::guard('staff')->user()->update([
            'is_available' => true,
            'unavailable_from' => null,
            'unavailable_until' => null,
        ]);

        return back()->with('success', 'Status aktif kembali.');
    }
}

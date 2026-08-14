<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminOverrideLog;
use App\Models\PregnantUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Flows.md §21.2.3 & §26.5: pemulihan akses ibu hamil yang kehilangan
 * nomor HP sepenuhnya. Diverifikasi manual di luar sistem (bidan/admin
 * tatap muka), lalu admin override nomor di sini — wajib isi alasan
 * & bukti verifikasi, dicatat ke admin_override_logs untuk audit UU PDP.
 */
class PhoneOverrideController extends Controller
{
    public function index(Request $request): Response
    {
        $query = trim((string) $request->query('q', ''));

        return Inertia::render('Admin/GantiNomor', [
            'query' => $query,
            'results' => $query === '' ? [] : PregnantUser::query()
                ->where('phone_number', 'like', "%{$query}%")
                ->orWhere('full_name', 'like', "%{$query}%")
                ->limit(10)
                ->get(['id', 'full_name', 'phone_number']),
            'recentOverrides' => AdminOverrideLog::query()
                ->with('pregnantUser:id,full_name')
                ->latest('performed_at')
                ->limit(10)
                ->get()
                ->map(fn (AdminOverrideLog $log) => [
                    'id' => $log->id,
                    'mother_name' => $log->pregnantUser->full_name,
                    'old_phone_number' => $log->old_phone_number,
                    'new_phone_number' => $log->new_phone_number,
                    'reason' => $log->reason,
                ]),
        ]);
    }

    public function store(Request $request, PregnantUser $pregnantUser): RedirectResponse
    {
        $data = $request->validate([
            'new_phone_number' => ['required', 'string', 'max:20', 'unique:pregnant_users,phone_number'],
            'reason' => ['required', 'string', 'min:10'],
        ]);

        abort_if($data['new_phone_number'] === $pregnantUser->phone_number, 422, 'Nomor baru sama dengan nomor lama.');

        DB::transaction(function () use ($pregnantUser, $data) {
            AdminOverrideLog::create([
                'admin_id' => Auth::guard('admin')->id(),
                'pregnant_user_id' => $pregnantUser->id,
                'old_phone_number' => $pregnantUser->phone_number,
                'new_phone_number' => $data['new_phone_number'],
                'reason' => $data['reason'],
                'performed_at' => now(),
            ]);

            $pregnantUser->update(['phone_number' => $data['new_phone_number']]);
        });

        return redirect()->route('admin.ganti-nomor.index')->with('success', "Nomor HP {$pregnantUser->full_name} berhasil diubah.");
    }
}

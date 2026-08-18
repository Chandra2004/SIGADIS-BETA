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
 * Controller untuk Pemulihan Akun & Override Akses Ibu Hamil (Point 6).
 * Membantu ibu hamil yang kehilangan akses nomor HP/perangkat melalui verifikasi identitas,
 * serta memulihkan akun ibu hamil yang terhapus mandiri (Soft Delete),
 * dengan mencatat rekam jejak audit permanen sesuai kepatuhan regulasi UU Perlindungan Data Pribadi (UU PDP).
 */
class PhoneOverrideController extends Controller
{
    public function index(Request $request): Response
    {
        $query = trim((string) $request->query('q', ''));

        $results = PregnantUser::withTrashed()
            ->when($query !== '', function ($q) use ($query) {
                $q->where('phone_number', 'like', "%{$query}%")
                    ->orWhere('full_name', 'like', "%{$query}%");
            })
            ->with(['pregnancies' => fn ($p) => $p->withTrashed()->latest()])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (PregnantUser $u) => [
                'id' => $u->id,
                'full_name' => $u->full_name,
                'phone_number' => $u->phone_number,
                'is_deleted' => $u->trashed(),
                'deleted_at' => $u->deleted_at?->format('d/m/Y H:i'),
                'current_pregnancy' => $u->pregnancies->first() ? [
                    'mother_name' => $u->pregnancies->first()->mother_name,
                    'region_code' => $u->pregnancies->first()->region_code,
                    'status' => $u->pregnancies->first()->status,
                    'due_date' => $u->pregnancies->first()->estimated_due_date?->toDateString(),
                ] : null,
                'created_at' => $u->created_at?->format('d/m/Y') ?? '-',
            ]);

        $recentOverrides = AdminOverrideLog::query()
            ->with(['pregnantUser' => fn ($q) => $q->withTrashed(), 'admin:id,full_name,institution'])
            ->latest('performed_at')
            ->limit(20)
            ->get()
            ->map(fn (AdminOverrideLog $log) => [
                'id' => $log->id,
                'mother_name' => $log->pregnantUser?->full_name ?? 'Ibu Hamil',
                'old_phone_number' => $log->old_phone_number,
                'new_phone_number' => $log->new_phone_number,
                'reason' => $log->reason,
                'admin_name' => $log->admin?->full_name ?? 'Administrator',
                'performed_at' => $log->performed_at?->diffForHumans() ?? 'Baru saja',
                'performed_date' => $log->performed_at?->format('d/m/Y H:i') ?? '-',
            ]);

        return Inertia::render('Admin/GantiNomor', [
            'query' => $query,
            'results' => $results,
            'recentOverrides' => $recentOverrides,
            'metrics' => [
                'total_mothers' => PregnantUser::withTrashed()->count(),
                'total_active' => PregnantUser::count(),
                'total_deleted' => PregnantUser::onlyTrashed()->count(),
                'total_overrides' => AdminOverrideLog::count(),
            ],
        ]);
    }

    public function store(Request $request, PregnantUser $pregnantUser): RedirectResponse
    {
        $data = $request->validate([
            'new_phone_number' => ['required', 'string', 'max:20', 'unique:pregnant_users,phone_number'],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
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

        return redirect()->route('admin.ganti-nomor.index')->with('success', "Nomor HP {$pregnantUser->full_name} berhasil diperbarui menjadi {$data['new_phone_number']}. Log audit UU PDP tercatat.");
    }

    public function restore(Request $request, $id): RedirectResponse
    {
        $pregnantUser = PregnantUser::withTrashed()->findOrFail($id);

        DB::transaction(function () use ($pregnantUser) {
            $pregnantUser->restore();
            $pregnantUser->pregnancies()->withTrashed()->restore();

            foreach ($pregnantUser->pregnancies as $preg) {
                $preg->consents()->update([
                    'revoked_at' => null,
                    'data_deletion_requested_at' => null,
                ]);
            }

            AdminOverrideLog::create([
                'admin_id' => Auth::guard('admin')->id(),
                'pregnant_user_id' => $pregnantUser->id,
                'old_phone_number' => $pregnantUser->phone_number,
                'new_phone_number' => $pregnantUser->phone_number,
                'reason' => 'Pemulihan akun & reaktivasi data ibu hamil (Restore Soft Delete)',
                'performed_at' => now(),
            ]);
        });

        return redirect()->route('admin.ganti-nomor.index')->with('success', "Akun {$pregnantUser->full_name} berhasil dipulihkan dan diaktifkan kembali.");
    }
}

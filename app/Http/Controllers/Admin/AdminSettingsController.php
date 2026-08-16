<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk Pengaturan Sistem & Tata Kelola Admin (Point 8).
 * Mengatur akun administrator sistem, profil institusi pelayanan kesehatan,
 * dan parameter integrasi gateway serta eskalasi darurat.
 */
class AdminSettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $currentAdmin = Auth::guard('admin')->user();

        $admins = AdminUser::query()
            ->orderBy('id')
            ->get()
            ->map(fn (AdminUser $a) => [
                'id' => $a->id,
                'full_name' => $a->full_name,
                'email' => $a->email,
                'institution' => $a->institution ?? 'Puskesmas Sungai Raya',
                'is_current_user' => (int) $a->id === (int) $currentAdmin->id,
                'created_at' => $a->created_at?->format('d/m/Y') ?? '-',
            ]);

        // Pengaturan Sistem & Integrasi Operasional
        $systemSettings = [
            'app_version' => 'v2.0 Beta (Gemastik 2026)',
            'institution_name' => $currentAdmin->institution ?? 'Puskesmas Sungai Raya',
            'emergency_timeout_minutes' => 3,
            'whatsapp_gateway_status' => config('services.whatsapp.enabled', true) ? 'Simulasi Aktif (Local Dev)' : 'Gateway Terhubung',
            'database_connection' => config('database.default'),
            'server_environment' => config('app.env'),
        ];

        return Inertia::render('Admin/Pengaturan', [
            'admins' => $admins,
            'currentAdmin' => [
                'id' => $currentAdmin->id,
                'full_name' => $currentAdmin->full_name,
                'email' => $currentAdmin->email,
                'institution' => $currentAdmin->institution ?? 'Puskesmas Sungai Raya',
            ],
            'systemSettings' => $systemSettings,
        ]);
    }

    public function storeAdmin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:admin_users,email'],
            'password' => ['required', 'string', 'min:8'],
            'institution' => ['nullable', 'string', 'max:150'],
        ]);

        $admin = AdminUser::create([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'institution' => $data['institution'] ?: 'Puskesmas Sungai Raya',
        ]);

        return back()->with('success', "Akun Administrator {$admin->full_name} ({$admin->email}) berhasil dibuat.");
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'institution' => ['required', 'string', 'max:150'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $updatePayload = [
            'full_name' => $data['full_name'],
            'institution' => $data['institution'],
        ];

        if (! empty($data['password'])) {
            $updatePayload['password_hash'] = Hash::make($data['password']);
        }

        $admin->update($updatePayload);

        return back()->with('success', 'Profil dan informasi institusi administrator berhasil diperbarui.');
    }

    public function destroyAdmin(Request $request, $adminUser): RedirectResponse
    {
        $targetAdmin = AdminUser::findOrFail(is_object($adminUser) ? $adminUser->id : $adminUser);
        $currentAdmin = Auth::guard('admin')->user();
        abort_if((int) $targetAdmin->id === (int) $currentAdmin->id, 422, 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');

        $name = $targetAdmin->full_name;
        $targetAdmin->delete();

        return back()->with('success', "Akun administrator {$name} berhasil dihapus dari sistem.");
    }
}

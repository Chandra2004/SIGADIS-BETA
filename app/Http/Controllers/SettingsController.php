<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Pengaturan Aplikasi (desain Figma) — sisi ibu hamil. Sengaja TANPA
 * pengaturan bahasa: sistem selalu Bahasa Indonesia yang sederhana,
 * tanpa istilah medis berat, buat semua pengguna.
 */
class SettingsController extends Controller
{
    public function show(): Response
    {
        $user = Auth::guard('pregnant')->user();

        return Inertia::render('Kehamilan/Pengaturan', [
            'settings' => $user->only(
                'text_size',
                'tts_enabled',
                'screening_reminder_enabled',
            ),
            'profilePhotoUrl' => $user->profilePhotoUrl(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'text_size' => ['required', 'in:normal,besar'],
            'tts_enabled' => ['required', 'boolean'],
            'screening_reminder_enabled' => ['required', 'boolean'],
        ]);

        Auth::guard('pregnant')->user()->update($data);

        return back()->with('success', 'Pengaturan disimpan.');
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'photo' => ['required', 'image', 'max:2048'],
        ]);

        $user = Auth::guard('pregnant')->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $path = $request->file('photo')->store('profile-photos', 'public');
        $user->update(['profile_photo_path' => $path]);

        return back()->with('success', 'Foto profil diperbarui.');
    }

    public function destroyPhoto(): RedirectResponse
    {
        $user = Auth::guard('pregnant')->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->update(['profile_photo_path' => null]);
        }

        return back()->with('success', 'Foto profil dihapus.');
    }
}

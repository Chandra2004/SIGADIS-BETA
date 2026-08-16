<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function index(): Response
    {
        $worker = Auth::guard('staff')->user();

        return Inertia::render('Desktop/Profile', [
            'worker' => [
                'id' => $worker->id,
                'full_name' => $worker->full_name,
                'phone_number' => $worker->phone_number,
                'role' => $worker->role,
                'status' => $worker->status,
                'str_number' => $worker->str_number,
                'appointment_letter_ref' => $worker->appointment_letter_ref,
                'region_code' => $worker->region_code,
                'is_available' => (bool) $worker->is_available,
            ],
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $worker = Auth::guard('staff')->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        if (! Hash::check($data['current_password'], $worker->password_hash)) {
            return back()->withErrors(['current_password' => 'Kata sandi saat ini tidak cocok.']);
        }

        $worker->update([
            'password_hash' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }
}

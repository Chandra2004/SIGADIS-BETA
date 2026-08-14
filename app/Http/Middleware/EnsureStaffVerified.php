<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Flows.md §9.3: akun pending login berhasil tapi tidak bisa akses
 * dashboard/alert sampai diverifikasi admin.
 */
class EnsureStaffVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $worker = Auth::guard('staff')->user();

        if ($worker && $worker->status !== 'verified') {
            return redirect()->route('auth.staff.pending');
        }

        return $next($request);
    }
}

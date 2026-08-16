<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatform
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $platform  'web' or 'mobile'
     */
    public function handle(Request $request, Closure $next, string $platform): Response
    {
        $isMobile = $request->header('X-Is-Native') === '1'
            || in_array($request->header('X-Capacitor-Platform'), ['android', 'ios'])
            || str_contains($request->userAgent() ?? '', 'Capacitor')
            || str_contains($request->userAgent() ?? '', 'wv')
            || $request->header('X-Platform') === 'mobile'
            || $request->query('platform') === 'mobile'
            || app()->environment('local');

        if ($platform === 'mobile' && !$isMobile) {
            // Jika rute khusus mobile tapi diakses dari web browser biasa di production
            return redirect()->route('landing.home');
        }

        if ($platform === 'web' && $isMobile && !app()->environment('local')) {
            // Jika rute khusus web tapi diakses dari aplikasi mobile di production
            if (\Illuminate\Support\Facades\Route::has('splash')) {
                return redirect()->route('splash');
            }
            if (\Illuminate\Support\Facades\Route::has('mobile.login.show')) {
                return redirect()->route('mobile.login.show');
            }
            if (\Illuminate\Support\Facades\Route::has('onboarding')) {
                return redirect()->route('onboarding');
            }
            return abort(403, 'Halaman ini hanya tersedia di Web Browser.');
        }

        return $next($request);
    }
}

<?php

use App\Http\Middleware\EnsurePlatform;
use App\Http\Middleware\EnsureStaffVerified;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'staff.verified' => EnsureStaffVerified::class,
            'platform' => EnsurePlatform::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('admin*')) {
                return route('auth.admin.login.show');
            }
            if ($request->is('bidan*')) {
                return route('auth.staff.login.show');
            }
            if ($request->is('mobile*')) {
                return route('mobile.login.show');
            }

            return route('auth.pregnant.phone.show');
        });

        $middleware->redirectUsersTo(function ($request) {
            if (Auth::guard('admin')->check()) {
                return route('admin.dashboard');
            }
            if (Auth::guard('staff')->check()) {
                return route('bidan.dashboard');
            }
            if (Auth::guard('pregnant')->check()) {
                return route('mobile.dashboard');
            }

            return route('landing.home');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

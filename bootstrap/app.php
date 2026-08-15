<?php

use App\Http\Middleware\EnsurePlatform;
use App\Http\Middleware\EnsureStaffVerified;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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

            return $request->is('bidan*')
                ? route('auth.staff.login.show')
                : route('auth.pregnant.phone.show');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

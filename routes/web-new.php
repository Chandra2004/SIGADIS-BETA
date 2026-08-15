<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Landing Routes
|--------------------------------------------------------------------------
| Tiap section besar dari landing page lama sekarang jadi halaman sendiri.
| Semua render lewat Inertia dan otomatis pakai Layouts/LandingLayout.vue
| lewat masing-masing Page karena sudah dibungkus <LandingLayout> di sana.
*/

// Route test untuk memverifikasi Vue + Inertia.js berjalan di server
Route::get('/test', function () {
    return Inertia::render('Test');
})->name('test');

Route::get('/', function () {
    return Inertia::render('Landing/Home');
})->name('landing.home');

Route::get('/tentang', function () {
    return Inertia::render('Landing/About');
})->name('landing.about');

Route::get('/fitur', function () {
    return Inertia::render('Landing/Features');
})->name('landing.features');

Route::get('/cara-kerja', function () {
    return Inertia::render('Landing/HowItWorks');
})->name('landing.how-it-works');

Route::get('/faq', function () {
    return Inertia::render('Landing/Faq');
})->name('landing.faq');

Route::get('/download-apk', function () {
    return Inertia::render('Landing/DownloadApk');
})->name('landing.download-apk');

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
| Halaman Login & Register di paket ini (resources/js/Pages/Auth/Login.vue
| dan Register.vue) sengaja ditaruh di path standar Laravel Breeze, supaya
| kalau project kamu sudah pakai Breeze, controller yang ada otomatis
| merender file baru ini tanpa perlu ubah routing apa pun — Breeze
| mem-Inertia::render('Auth/Login') dan Inertia::render('Auth/Register').
|
| routes/auth.php bawaan Breeze biasanya sudah berisi persis blok di bawah
| ini (via middleware 'guest'). Kalau file itu SUDAH ada & sudah di-require
| dari routes/web.php atau bootstrap/app.php, JANGAN duplikasi — cukup
| pastikan controller-nya tetap AuthenticatedSessionController &
| RegisteredUserController seperti biasa.
|
| Kalau belum pakai Breeze sama sekali, install dulu:
|   composer require laravel/breeze --dev
|   php artisan breeze:install vue
| supaya controller & POST handler (hashing password, session, dsb) sudah
| tersedia dan aman, alih-alih ditulis manual di sini.
*/

if (! Route::has('login')) {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])
            ->name('login');

        Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);

        Route::get('/register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])
            ->name('register');

        Route::post('/register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'store']);

        Route::get('/forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])
            ->name('password.request');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
            ->name('logout');
    });
}

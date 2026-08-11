<?php

use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Splash'))->name('splash');
Route::get('/welcome', [WelcomeController::class, 'index'])->name('welcome');

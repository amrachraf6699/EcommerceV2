<?php

use App\Http\Controllers\Admin\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::controller(AuthController::class)->group(function (): void {
        Route::get('/login', 'create')->name('login');
        Route::post('/login', 'store')->name('login.store');
    });
});

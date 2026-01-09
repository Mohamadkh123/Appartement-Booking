<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Session;

Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar'])) {
        Session::put('locale', $locale);
    }
    return redirect()->back();
});

Route::get('/', [AdminController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminController::class, 'login']);
Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index']);
    Route::get('/admin/users', [AdminController::class, 'users']);
    Route::get('/admin/users/pending', [AdminController::class, 'pendingUsers']);
    Route::post('/admin/users/{user}/approve', [AdminController::class, 'approveUser']);
    Route::post('/admin/users/{user}/reject', [AdminController::class, 'rejectUser']);
    Route::post('/admin/wallet/deposit/{user}', [AdminController::class, 'deposit']);
});

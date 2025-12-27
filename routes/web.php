<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/', [AdminController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminController::class, 'login']);
Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users.index');
    Route::get('/admin/users/pending', [AdminController::class, 'pendingUsers'])->name('admin.users.pending');
    Route::post('/admin/users/{user}/approve', [AdminController::class, 'approveUser']);
    Route::post('/admin/users/{user}/reject', [AdminController::class, 'rejectUser']);
    Route::post('/admin/wallet/deposit/{user}', [AdminController::class, 'deposit']);
});




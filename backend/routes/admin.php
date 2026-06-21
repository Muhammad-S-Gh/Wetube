<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/admin', [AuthController::class, 'index']);

Route::prefix('/admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->middleware('guest:web')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:web')->name('logout');
});

Route::middleware(['auth:web', 'admin'])->prefix('/admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Users Management
    Route::get('/users', [DashboardController::class, 'users'])->name('users');
    Route::post('/users/{user}/delete', [DashboardController::class, 'deleteUser'])->name('users.delete');

    // Videos Management
    Route::get('/videos', [DashboardController::class, 'videos'])->name('videos');
    Route::get('/videos/{video}', [DashboardController::class, 'show'])->name('videos.show');
    Route::post('/videos/{video}/delete', [DashboardController::class, 'deleteVideo'])->name('videos.delete');

    // Alerts Management
    Route::get('/alerts', [DashboardController::class, 'alerts'])->name('alerts');
    Route::post('/alerts/{alert}/approve', [DashboardController::class, 'approveAlert'])->name('alerts.approve');

    // Delete Admin Account
    Route::delete('/account', [AuthController::class, 'destroyAccount'])->name('account.destroy');
});

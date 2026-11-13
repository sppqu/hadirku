<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect root to appropriate dashboard or login
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->role === 'admin' ? redirect('/admin') : redirect('/dashboard');
    }
    return redirect('/login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:6,1');
Route::post('/admin/login', [AuthController::class, 'loginAdmin'])->name('admin.login.post')->middleware('throttle:6,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// User Mobile Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('user.dashboard');
    Route::get('/history', [DashboardController::class, 'history'])->name('user.history');
    Route::get('/punch', [DashboardController::class, 'punchPage'])->name('user.punch');
    Route::post('/punch', [DashboardController::class, 'punch'])->name('user.punch.store');
    Route::get('/leaves', [DashboardController::class, 'leaves'])->name('user.leaves');
    Route::post('/leaves', [DashboardController::class, 'storeLeave'])->name('user.leaves.store');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('user.profile');
    Route::post('/profile/avatar', [DashboardController::class, 'updateAvatar'])->name('user.profile.avatar');
    Route::post('/profile/password', [DashboardController::class, 'updatePassword'])->name('user.profile.password');
    Route::post('/profile/face-register', [DashboardController::class, 'registerFace'])->name('user.profile.face_register');
    Route::post('/profile/face-unlock-request', [DashboardController::class, 'requestFaceUnlock'])->name('user.profile.face_unlock_request');
});

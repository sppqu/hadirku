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
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// User Mobile Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('user.dashboard');
    Route::get('/history', [DashboardController::class, 'history'])->name('user.history');
    Route::post('/punch', [DashboardController::class, 'punch'])->name('user.punch');
    Route::get('/leaves', [DashboardController::class, 'leaves'])->name('user.leaves');
    Route::post('/leaves', [DashboardController::class, 'storeLeave'])->name('user.leaves.store');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('user.profile');
});

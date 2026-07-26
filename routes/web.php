<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\MemberLoginController;
use App\Http\Controllers\Auth\StaffLoginController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Staff (Admin Panel) authentication
Route::middleware('guest:web')->group(function () {
    Route::get('/login', [StaffLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [StaffLoginController::class, 'login']);
});
Route::post('/logout', [StaffLoginController::class, 'logout'])->middleware('auth:web')->name('logout');

// Member Portal authentication
Route::middleware('guest:member')->group(function () {
    Route::get('/member/login', [MemberLoginController::class, 'showLoginForm'])->name('member.login');
    Route::post('/member/login', [MemberLoginController::class, 'login']);
});
Route::post('/member/logout', [MemberLoginController::class, 'logout'])->middleware('member.auth:member')->name('member.logout');

// Admin Panel
Route::middleware(['auth:web'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
});

// Member Portal
Route::middleware(['member.auth:member'])->prefix('member')->name('member.')->group(function () {
    Route::get('/dashboard', [MemberDashboardController::class, 'index'])->name('dashboard');
});

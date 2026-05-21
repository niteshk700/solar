<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\SettingsController;

// 1. Guest Routes (Authentication)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// 2. Protected Admin Routes
Route::middleware('auth')->group(function () {
    // Session Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard Home
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/live', [DashboardController::class, 'liveData'])->name('dashboard.live');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
    Route::get('/dashboard/solar-export', [DashboardController::class, 'exportSolar'])->name('dashboard.solar-export');

    // Device Management CRUD
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::post('/devices', [DeviceController::class, 'store'])->name('devices.store');
    Route::put('/devices/{id}', [DeviceController::class, 'update'])->name('devices.update');
    Route::delete('/devices/{id}', [DeviceController::class, 'destroy'])->name('devices.destroy');

    // Device Analytics Telemetry
    Route::get('/device/{id}/analytics', [DeviceController::class, 'analytics'])->name('devices.analytics');

    // Platform & Account Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/tokens', [SettingsController::class, 'generateApiToken'])->name('settings.tokens.generate');
    Route::delete('/settings/tokens/{id}', [SettingsController::class, 'deleteApiToken'])->name('settings.tokens.delete');
});

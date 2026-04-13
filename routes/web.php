<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ─── Public ──────────────────────────────────────────────────────────────────

Route::get('/', function () {
    return view('welcome');
});

// ─── Authenticated ───────────────────────────────────────────────────────────

Route::middleware(['auth', 'verified', 'account.locked'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // ── Profile ──────────────────────────────────────────────────────────────
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',        [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/',      [ProfileController::class, 'update'])->name('update');
        Route::delete('/',     [ProfileController::class, 'destroy'])->name('destroy');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');

        // 2FA
        Route::post('/two-factor/enable',  [ProfileController::class, 'enableTwoFactor'])->name('two-factor.enable');
        Route::post('/two-factor/confirm', [ProfileController::class, 'confirmTwoFactor'])->name('two-factor.confirm');
        Route::delete('/two-factor',       [ProfileController::class, 'disableTwoFactor'])->name('two-factor.disable');

        // Sessions
        Route::delete('/sessions/{device}', [ProfileController::class, 'revokeSession'])->name('sessions.revoke');
    });

    // ── Admin Area ───────────────────────────────────────────────────────────
    Route::prefix('admin')->name('admin.')->middleware(['role:admin|super-admin', 'rank:8'])->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // User Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/',                    [UserController::class, 'index'])->name('index');
            Route::get('/create',              [UserController::class, 'create'])->name('create');
            Route::post('/',                   [UserController::class, 'store'])->name('store');
            Route::get('/{user}/edit',         [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}',              [UserController::class, 'update'])->name('update');
            Route::delete('/{user}',           [UserController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/restore',       [UserController::class, 'restore'])->name('restore');
            Route::patch('/{user}/status',     [UserController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/export/csv',          [UserController::class, 'exportCsv'])->name('export-csv');
            Route::post('/import/csv',         [UserController::class, 'importCsv'])->name('import-csv');
        });

        // Role Management (super-admin only)
        Route::prefix('roles')->name('roles.')->middleware('role:super-admin')->group(function () {
            Route::get('/',           [RoleController::class, 'index'])->name('index');
            Route::get('/create',     [RoleController::class, 'create'])->name('create');
            Route::post('/',          [RoleController::class, 'store'])->name('store');
            Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
            Route::put('/{role}',     [RoleController::class, 'update'])->name('update');
            Route::delete('/{role}',  [RoleController::class, 'destroy'])->name('destroy');
        });

        // Activity Log
        Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log');
    });
});

require __DIR__ . '/auth.php';

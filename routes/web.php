<?php

use Illuminate\Support\Facades\Route;
use MartinLechene\LedgerManager\Http\Controllers\DashboardController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('ledger-dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('ledger.dashboard');
        Route::get('/devices', [DashboardController::class, 'devices'])->name('ledger.devices');
        Route::get('/devices/{id}', [DashboardController::class, 'deviceShow'])->name('ledger.device.show');
        Route::get('/accounts', [DashboardController::class, 'accounts'])->name('ledger.accounts');
        Route::get('/transactions', [DashboardController::class, 'transactions'])->name('ledger.transactions');
        Route::get('/security', [DashboardController::class, 'security'])->name('ledger.security');
        Route::get('/api-docs', [DashboardController::class, 'apiDocumentation'])->name('ledger.api-docs');
    });
});


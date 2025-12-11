<?php

use Illuminate\Support\Facades\Route;
use YourVendor\LedgerManager\Http\Controllers\API\DevicesController;
use YourVendor\LedgerManager\Http\Controllers\API\AccountsController;
use YourVendor\LedgerManager\Http\Controllers\API\TransactionsController;
use YourVendor\LedgerManager\Http\Controllers\API\AuditController;

Route::middleware(['api', 'throttle:60,1'])->prefix('ledger')->group(function () {
    // Devices
    Route::get('/devices/discover/{transport?}', [DevicesController::class, 'discover']);
    Route::get('/devices', [DevicesController::class, 'list']);
    Route::get('/devices/{id}', [DevicesController::class, 'show']);
    Route::post('/devices/{id}/connect', [DevicesController::class, 'connect']);
    Route::post('/devices/{id}/disconnect', [DevicesController::class, 'disconnect']);

    // Accounts
    Route::get('/accounts', [AccountsController::class, 'list']);
    Route::get('/accounts/{id}', [AccountsController::class, 'show']);
    Route::post('/accounts/generate', [AccountsController::class, 'generateAddresses']);

    // Transactions
    Route::get('/transactions', [TransactionsController::class, 'list']);
    Route::get('/transactions/{id}', [TransactionsController::class, 'show']);
    Route::post('/transactions/sign', [TransactionsController::class, 'sign']);

    // Audit & Security
    Route::get('/audit', [AuditController::class, 'audit']);
    Route::get('/audit/logs/{days?}', [AuditController::class, 'logs']);
    Route::get('/audit/statistics', [AuditController::class, 'statistics']);
});


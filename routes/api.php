<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MasterController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\ValidationController;
use App\Http\Controllers\Api\AccountingController;
use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\ExportKecamatanController;

/*
|--------------------------------------------------------------------------
| API Routes - BUMDesa Kuala Alam Management System
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Public verification for QR Code / Digital Signature audit
Route::get('/reports/{id}/verify-public', [ApprovalController::class, 'verify']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth status
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Master Data & Dashboard
    Route::prefix('master')->group(function () {
        Route::get('/units', [MasterController::class, 'units']);
        Route::get('/accounts', [MasterController::class, 'accounts']);
        Route::get('/dashboard', [MasterController::class, 'summaryDashboard']);
    });

    // Transactions & Cash Book
    Route::post('/transactions/calculate-guided', [TransactionController::class, 'calculateGuided']);
    Route::get('/transactions/cash-book', [TransactionController::class, 'cashBook']);
    Route::apiResource('transactions', TransactionController::class)->only(['index', 'store', 'show']);

    // Validation (Bendahara Umum)
    Route::prefix('validation')->group(function () {
        Route::get('/pending', [ValidationController::class, 'pending']);
        Route::post('/{id}/approve', [ValidationController::class, 'approve']);
        Route::post('/{id}/reject', [ValidationController::class, 'reject']);
    });

    // Accounting & SAK ETAP Financial Statements
    Route::prefix('accounting')->group(function () {
        Route::get('/journal', [AccountingController::class, 'generalJournal']);
        Route::get('/ledger', [AccountingController::class, 'ledger']);
        Route::get('/trial-balance', [AccountingController::class, 'trialBalance']);
        Route::get('/income-statement', [AccountingController::class, 'incomeStatement']);
        Route::get('/balance-sheet', [AccountingController::class, 'balanceSheet']);
    });

    // Director Approval & Digital Signatures
    Route::prefix('reports')->group(function () {
        Route::get('/draft', [ApprovalController::class, 'draft']);
        Route::post('/{id}/submit', [ApprovalController::class, 'submit']);
        Route::post('/{id}/approve', [ApprovalController::class, 'approve']);
        Route::get('/{id}/verify', [ApprovalController::class, 'verify']);
        Route::get('/export-kecamatan', [ExportKecamatanController::class, 'export']);
    });
});

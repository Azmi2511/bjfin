<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ValidationController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\TransaksiController;

/*
|--------------------------------------------------------------------------
| Web Routes - BUMDesa Kuala Alam Management Web Portal
|--------------------------------------------------------------------------
| Arsitektur modular per fitur: Dashboard, Validasi, Transaksi, Laporan, & Profil.
*/

Route::middleware('auth')->group(function () {
    // 1. Dashboard Eksekutif
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Verifikasi & Validasi Transaksi (Otoritas Bendahara Umum)
    Route::prefix('validation')->name('validation')->group(function () {
        Route::get('/', [ValidationController::class, 'index']);
        Route::post('{id}/approve', [ValidationController::class, 'approve'])->name('.approve');
        Route::post('{id}/reject', [ValidationController::class, 'reject'])->name('.reject');
    });

    // 3. Pelaporan SAK ETAP & Ekspor Excel Format Resmi Pemerintah
    Route::prefix('reports')->name('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::get('download-excel', [ReportController::class, 'downloadExcel'])->name('.download-excel');
        Route::post('{id}/submit', [ReportController::class, 'submitReport'])->name('.submit');
        Route::post('{id}/approve', [ReportController::class, 'approveReport'])->name('.approve');
        Route::get('{id}/verify', [ReportController::class, 'verifyReport'])->name('.verify');
    });

    // 4. Pencatatan Transaksi (Unit Lapangan & Kas Pusat BUMDesa)
    Route::prefix('transaksi')->name('transaksi.')->group(function () {
        // Pencatatan Lapangan Unit Usaha (Wifi, USP, Kebun)
        Route::get('unit-input', [TransaksiController::class, 'unitInput'])->name('unit-input');
        Route::post('unit-store', [TransaksiController::class, 'storeUnitTransaction'])->name('unit-store');
        Route::post('master-store', [TransaksiController::class, 'storeMasterEntry'])->name('master-store');
        Route::delete('master-destroy/{id}', [TransaksiController::class, 'destroyMasterEntry'])->name('master-destroy');

        // Pencatatan Kas Pusat & Memorial (Bendahara Umum)
        Route::get('umum-input', [TransaksiController::class, 'umumInput'])->name('umum-input');
        Route::post('umum-store', [TransaksiController::class, 'storeUmumTransaction'])->name('umum-store');
    });

    // 5. User Profile & 2FA Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Diagnostic error testing routes
Route::get('/test-error-500', function () { abort(500); });
Route::get('/test-error-403', function () { abort(403); });
Route::get('/test-error-401', function () { abort(401); });
Route::get('/test-error-419', function () { abort(419); });
Route::get('/test-error-429', function () { abort(429); });

require __DIR__.'/auth.php';

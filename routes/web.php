<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryExportController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/analitic', [DashboardController::class, 'analitic'])->name('analitic');
Route::get('/riwayat', [DashboardController::class, 'riwayat'])->name('riwayat');
Route::get('/riwayat/export/excel', [HistoryExportController::class, 'excel'])->name('riwayat.export.excel');
Route::get('/riwayat/export/pdf', [HistoryExportController::class, 'pdf'])->name('riwayat.export.pdf');

Route::get('/cek-firebase', function () {
    $path = storage_path('app/firebase-credentials.json');
    $base64 = getenv('FIREBASE_CREDENTIALS_BASE64') ?: env('FIREBASE_CREDENTIALS_BASE64');

    return response()->json([
        'env_terdeteksi' => !empty($base64),
        'file_berhasil_dibuat' => file_exists($path),
        'lokasi_file' => $path,
        'ukuran_file_bytes' => file_exists($path) ? filesize($path) : 0,
    ]);
});
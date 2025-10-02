<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AntrianController;
use App\Http\Controllers\Api\PetugasController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\Api\TamuController;

Route::middleware('auth:sanctum')->group(function () {
    // Rute untuk mendapatkan data pengguna yang sedang login (digunakan oleh auth.js)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // ✅ Rute Dashboard harus di sini agar dilindungi
    Route::get('/petugas', [PetugasController::class, 'index']);
    Route::get('/antrian', [AntrianController::class, 'index']);
    Route::get('/statistik/mingguan', [StatistikController::class, 'mingguan']);
    Route::get('/statistik/bulanan', [StatistikController::class, 'bulanan']);

        // ✅ Rute Daftar Tamu
    Route::get('/tamu', [TamuController::class, 'index']);
    Route::delete('/tamu/{id}', [TamuController::class, 'destroy']);
    
    // Rute export tidak perlu middleware auth:sanctum 
    // jika diakses via window.open, tapi sebaiknya tetap dicek otentikasi di controller.
    Route::get('/tamu/export/pdf', [TamuController::class, 'exportPdf']);
    Route::get('/tamu/export/csv', [TamuController::class, 'exportCsv']);
});

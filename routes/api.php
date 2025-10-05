<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AntrianController;
use App\Http\Controllers\Api\PetugasController;
use App\Http\Controllers\Api\StatistikController;
use App\Http\Controllers\Api\TamuController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Api\KunjunganController;
use App\Http\Controllers\Api\LayananController;
use App\Http\Controllers\Api\LaporanPiketController;
use App\Http\Controllers\Api\RatingPetugasController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ✅ PERBAIKAN: Definisikan rute login secara eksplisit hanya untuk metode POST
Route::post('/login', LoginController::class);

// Grup ini memastikan semua rute di dalamnya memerlukan autentikasi Sanctum.
Route::middleware('auth:sanctum')->group(function () {
    
    // Rute untuk mendapatkan data pengguna yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Rute-rute untuk Dashboard
    Route::get('/petugas', [PetugasController::class, 'index']);
    Route::get('/antrian', [AntrianController::class, 'index']);
    Route::get('/statistik/mingguan', [StatistikController::class, 'mingguan']);
    Route::get('/statistik/bulanan', [StatistikController::class, 'bulanan']);

    // Rute-rute untuk Daftar Tamu
    Route::get('/tamu', [TamuController::class, 'index']);
    Route::delete('/tamu/{id}', [TamuController::class, 'destroy']);
    
    // Rute untuk ekspor data
    Route::get('/tamu/export/pdf', [TamuController::class, 'exportPdf']);
    Route::get('/tamu/export/csv', [TamuController::class, 'exportCsv']);

    // Kunjungan
    Route::get('/kunjungan/export/pdf', [\App\Http\Controllers\Api\KunjunganController::class, 'exportPdf']);
    Route::apiResource('/kunjungan', \App\Http\Controllers\Api\KunjunganController::class);

    Route::apiResource('/layanan', \App\Http\Controllers\Api\LayananController::class);

    Route::get('/piket/overview',               [PetugasController::class, 'piketOverview']);
    Route::post('/piket/swap-requests',         [PetugasController::class, 'storeSwap']);
    Route::get('/piket/swap-requests',          [PetugasController::class, 'listSwapRequests']);        // admin
    Route::post('/piket/swap-requests/{id}/decide', [PetugasController::class, 'decideSwapRequest']);   // admin

    Route::get('/piket/report/today',   [LaporanPiketController::class, 'today']);
    Route::post('/piket/report/start',  [LaporanPiketController::class, 'start']);
    Route::post('/piket/report/finish', [LaporanPiketController::class, 'finish']);
    Route::get( '/piket/report/history',[LaporanPiketController::class, 'history']);

    Route::post('/layanan/{id}/feedback-link', [RatingPetugasController::class, 'makeLink']);
    Route::get('/feedback/ratings/summary', [RatingPetugasController::class, 'summary']);
    Route::post('/feedback/ratings/{token}', [RatingPetugasController::class, 'submitByToken']);

});
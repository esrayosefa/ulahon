<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AntrianController;
use App\Http\Controllers\Api\PetugasController;
use App\Http\Controllers\StatistikController;

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
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/antrian', [AntrianController::class, 'index']);
});

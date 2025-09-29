<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PetugasController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\Api\AntrianController;
use App\Http\Controllers\TamuController;

Route::middleware('auth:sanctum')->get('/petugas', [PetugasController::class, 'index']);


Route::get('/statistik/mingguan', [StatistikController::class, 'mingguan']);
Route::get('/statistik/bulanan', [StatistikController::class, 'bulanan']);
Route::get('/antrian', [AntrianController::class, 'index']);

Route::get('/tamu/export/pdf',      [TamuController::class, 'exportPdf'])->name('tamu.export_pdf');
    Route::get('/tamu/export/csv',      [TamuController::class, 'exportCsv'])->name('tamu.export_csv');
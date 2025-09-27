<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PetugasController;

Route::middleware('auth:sanctum')->get('/petugas', [PetugasController::class, 'index']);

use App\Http\Controllers\StatistikController;
Route::get('/statistik/mingguan', [StatistikController::class, 'mingguan']);
Route::get('/statistik/bulanan', [StatistikController::class, 'bulanan']);

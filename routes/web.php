<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;

Route::get('/{any}', function () {
    return view('app'); 
})->where('any', '.*');
// ✅ Perbaiki route login: Panggil secara eksplisit method __invoke
Route::post('/login', [LoginController::class, '__invoke']);

// ✅ Tambahkan route logout: Panggil method logout
Route::post('/logout', [LoginController::class, 'logout']);
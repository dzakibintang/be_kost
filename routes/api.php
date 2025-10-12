<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Owner\KosController;
use App\Http\Controllers\Owner\KosFacilityController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Semua route API ada di sini.
| Kita pakai JWT untuk proteksi auth.
|
*/

// 🔑 AUTH ROUTES
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

// Untuk admin saja
Route::middleware(['auth:api', 'role:owner'])->group(function () {
    Route::get('/owner/dashboard', [AdminController::class, 'dashboard']);
});

// Untuk user biasa
Route::middleware(['auth:api', 'role:society'])->group(function () {
    Route::get('/society/dashboard', [UserController::class, 'dashboard']);
});

// 🔹 Route default untuk cek user login
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

 // CRUD KOST
Route::middleware(['auth:api', 'role:owner'])->prefix('owner')->group(function () {
    Route::get('/kos', [KosController::class, 'index']);       // list kos milik owner
    Route::post('/kos', [KosController::class, 'store']);      // tambah kos
    Route::get('/kos/{id}', [KosController::class, 'show']);   // detail kos
    Route::put('/kos/{id}', [KosController::class, 'update']); // update kos
    Route::delete('/kos/{id}', [KosController::class, 'destroy']); // hapus kos
});

Route::middleware('auth:api')->prefix('owner')->group(function() {
    Route::get('/kos/{kos_id}/facilities', [KosFacilityController::class, 'index']);
    Route::post('/kos/{kos_id}/facilities', [KosFacilityController::class, 'store']);
    Route::put('/facilities/{id}', [KosFacilityController::class, 'update']);
    Route::delete('/facilities/{id}', [KosFacilityController::class, 'destroy']);
});


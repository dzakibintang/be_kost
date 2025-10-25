<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Owner\KosController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewReplyController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\OwnerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Semua route API ada di sini. Kita pakai JWT untuk proteksi auth.
|
| Role:
| - owner   → admin / pemilik kos
| - society → user biasa / penyewa
|--------------------------------------------------------------------------
*/

// 🔑 AUTH ROUTES
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

/*
|--------------------------------------------------------------------------
| 🧑‍💼 OWNER (Admin Kos)
|--------------------------------------------------------------------------
| Prefix: /api/owner
| Proteksi: auth:api + role:owner
*/
Route::middleware(['auth:api', 'role:owner'])->prefix('owner')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);

    // ✅ CRUD Kos
    Route::get('/kos', [KosController::class, 'index']);
    Route::post('/kos', [KosController::class, 'store']);
    Route::get('/kos/{id}', [KosController::class, 'show']);
    Route::put('/kos/{id}', [KosController::class, 'update']);
    Route::delete('/kos/{id}', [KosController::class, 'destroy']);

    // 🖼️ Hapus satu gambar kos (tambahan)
    Route::delete('/kos/image/{imageId}', [KosController::class, 'deleteImage']);

    // ✅ CRUD Balasan Review (ReviewReply)
    Route::post('/review-reply', [ReviewReplyController::class, 'store']);
    Route::put('/review-reply/{id}', [ReviewReplyController::class, 'update']);
    Route::delete('/review-reply/{id}', [ReviewReplyController::class, 'destroy']);

    // ✅ BOOKING (Owner lihat & kelola booking)
    Route::get('/bookings', [BookingController::class, 'listByOwner']); // lihat semua booking di kos miliknya
    Route::put('/booking/{id}/status', [BookingController::class, 'updateStatus']); // ubah status booking (accept/reject)

    // ✅ OWNER PROFILE (lihat & update profil)
    Route::get('/profile', [OwnerController::class, 'show']);
    Route::put('/profile', [OwnerController::class, 'update']);
});

/*
|--------------------------------------------------------------------------
| 👥 SOCIETY (User Biasa / Penyewa)
|--------------------------------------------------------------------------
| Prefix: /api/society
| Proteksi: auth:api + role:society
*/
Route::middleware(['auth:api', 'role:society'])->prefix('society')->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard']);

    // 🔹 Review (buat user)
    Route::post('/review', [ReviewController::class, 'store']);
    Route::put('/review/{id}', [ReviewController::class, 'update']);
    Route::delete('/review/{id}', [ReviewController::class, 'destroy']);

    // ✅ BOOKING (Society / user)
    Route::post('/booking', [BookingController::class, 'store']); // buat booking
    Route::get('/bookings', [BookingController::class, 'myBookings']); // lihat semua booking milik user
    Route::get('/booking/{id}', [BookingController::class, 'show']); // lihat detail booking
    Route::get('/booking/{id}/nota', [BookingController::class, 'printNota']); // cetak bukti nota
});

/*
|--------------------------------------------------------------------------
| 🌐 PUBLIC (Bisa diakses tanpa login)
|--------------------------------------------------------------------------
| Untuk sekarang, publik hanya bisa lihat daftar dan detail kos.
*/
Route::get('/kos', [UserController::class, 'listKos']);
Route::get('/kos/{id}', [UserController::class, 'showKos']);

/*
|--------------------------------------------------------------------------
| 📘 Panduan Filter untuk endpoint GET /api/kos
|--------------------------------------------------------------------------
| Kamu bisa tambahkan query di URL:
| ?gender=female
| ?min_price=500000&max_price=1500000
| ?available_only=true
| ?sort=price_asc | price_desc | newest
|
| Contoh:
| http://localhost:8000/api/kos?gender=all&min_price=300000&sort=price_asc
*/

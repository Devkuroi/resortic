<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

/* ── Rutas públicas ──────────────────────────────────────────────────── */

Route::get('/', fn() => redirect()->route('login'));

Route::get('/login',   [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',  [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/* ── Rutas protegidas (requieren sesión activa) ───────────────────────── */

Route::middleware('auth')->group(function () {

    // Disponibilidad: todos los roles
    Route::get('reservations/availability', [ReservationController::class, 'availability'])
        ->name('reservations.availability');

    // Reservas: todos los roles
    Route::resource('reservations', ReservationController::class);

    // Habitaciones: admin y hotel
    Route::middleware('role:admin,hotel')->group(function () {
        Route::resource('rooms', RoomController::class);
        Route::patch('rooms/{room}/status', [RoomController::class, 'updateStatus'])
            ->name('rooms.status');
    });

    // Cuentas: solo admin
    Route::middleware('role:admin')->group(function () {
        Route::resource('accounts', AccountController::class);
    });
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ReservationController;

Route::get('/', fn() => redirect()->route('login'));

// Autenticación
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

// CRUD de cuentas
Route::resource('accounts', AccountController::class);

// CRUD de habitaciones + cambio de estado rápido
Route::resource('rooms', RoomController::class);
Route::patch('rooms/{room}/status', [RoomController::class, 'updateStatus'])->name('rooms.status');

// Disponibilidad (RF7) – debe ir ANTES del resource para no colisionar
Route::get('reservations/availability', [ReservationController::class, 'availability'])->name('reservations.availability');

// CRUD de reservas
Route::resource('reservations', ReservationController::class);

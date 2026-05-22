<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;

// Redirigir raíz al login
Route::get('/', fn() => redirect()->route('login'));

// Autenticación
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

// CRUD de cuentas (protegido por sesión dentro del controlador)
Route::resource('accounts', AccountController::class);

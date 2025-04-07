<?php

use App\Http\Controllers\Api\CardapioController;
use App\Http\Controllers\Api\ReservaController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('/users')->controller(UserController::class)->group(function() {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{user}', 'show')->middleware('auth:sanctum');
    Route::put('/{user}', 'update')->middleware('auth:sanctum');
    Route::delete('/{user}', 'destroy')->middleware('auth:sanctum');
});

Route::controller(TokenController::class)->group(function() {
    Route::post('/login', 'store');
    Route::delete('/logout', 'destroy')->middleware('auth:sanctum');
});

Route::prefix('/reservas')->controller(ReservaController::class)->middleware('auth:sanctum', 'checkRole')->group(function() {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{reserva}', 'show')->middleware('checkReserva');
    Route::put('/{reserva}', 'update')->middleware('checkReserva');
    Route::delete('/{reserva}', 'destroy')->middleware('checkReserva');
});

Route::prefix('/cardapio')->controller(CardapioController::class)->middleware('auth:sanctum', 'checkRole')->group(function() {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{prato}', 'show');
    Route::put('/{prato}', 'update');
    Route::delete('/{prato}', 'destroy');
});

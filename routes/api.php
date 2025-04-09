<?php

use App\Http\Controllers\Api\CardapioController;
use App\Http\Controllers\Api\ReservaController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\api\UserController;
use Illuminate\Support\Facades\Route;

// Rotas Públicas
Route::post('/users', [UserController::class, 'store']);
Route::post('/login', [TokenController::class, 'store']);

// Rotas Privadas
Route::middleware('auth:sanctum')->group(function() {
    Route::prefix('/users')->controller(UserController::class)->group(function() {
        Route::get('/', 'index');
        Route::get('/{user}', 'show');
        Route::put('/{user}', 'update');
        Route::delete('/{user}', 'destroy');
    });

    Route::prefix('/reservas')->controller(ReservaController::class)->group(function() {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{reserva}', 'show')->middleware('checkReserva');
        Route::put('/{reserva}', 'update')->middleware('checkReserva');
        Route::delete('/{reserva}', 'destroy')->middleware('checkReserva');
    });

    Route::prefix('/cardapio')->controller(CardapioController::class)->middleware('checkRole')->group(function() {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{prato}', 'show');
        Route::put('/{prato}', 'update');
        Route::delete('/{prato}', 'destroy');
    });

    Route::delete('/logout', [TokenController::class, 'destroy']);
});

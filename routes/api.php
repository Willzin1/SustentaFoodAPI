<?php

use App\Http\Controllers\Api\ReservaController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


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

Route::prefix('/reservas')->controller(ReservaController::class)->middleware('auth:sanctum', 'checkReserva')->group(function() {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{reserva}', 'show');
    Route::put('/{reserva}', 'update');
    Route::delete('/{reserva}', 'destroy');
});

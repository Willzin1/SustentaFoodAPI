<?php

use App\Http\Controllers\Api\CardapioController;
use App\Http\Controllers\Api\RelatorioReservaController;
use App\Http\Controllers\Api\ReservaController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\EmailVerificationController;
use Illuminate\Support\Facades\Route;

// Rotas Públicas
Route::post('/users', [UserController::class, 'store']);
Route::post('/login', [TokenController::class, 'store']);
Route::post('/reservas/notLoggedUser', [ReservaController::class, 'notLoggedUserStore']);
Route::get('/cardapio', [CardapioController::class, 'index']);
Route::get('/favoritos/favoritados', [FavoriteController::class, 'getMostFavoritedDishes']);

// Rotas Privadas
Route::middleware(['auth:sanctum', 'verified'])->group(function() {
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
        Route::post('/{reserva}/cancelar', 'cancel')->middleware('checkReserva');
    });

    Route::prefix('/cardapio')->controller(CardapioController::class)->middleware('checkRole')->group(function() {
        Route::post('/', 'store');
        Route::get('/{prato}', 'show');
        Route::put('/{prato}', 'update');
        Route::delete('/{prato}', 'destroy');
    });

    Route::delete('/logout', [TokenController::class, 'destroy']);

    Route::prefix('/relatorios/reservas')->controller(RelatorioReservaController::class)->group(function () {
        Route::get('/dia', 'getReservationsByDay');
        Route::get('/semana', 'getReservationsByWeek');
        Route::get('/mes', 'getReservationsByMonth');
    });

    // Rotas para favoritos (protegidas por autenticação)
    Route::prefix('/favoritos')->controller(FavoriteController::class)->group(function() {
        Route::post('/{pratoId}', 'toggleFavorite');
        Route::delete('/{pratoId}', 'destroy');
        Route::get('/', 'getUserFavorites');
        Route::get('/verificar/{pratoId}', 'checkIsFavorite');
    });
});

Route::controller(EmailVerificationController::class)->group(function() {
    Route::get('/confirmar-reserva/{token}', 'confirmReserva');
    Route::get('/email/verify/{id}/{hash}', 'verify')->middleware(['signed'])->name('verification.verify');
    Route::post('/email/verification-notification', 'resend')->name('verification.send');
});

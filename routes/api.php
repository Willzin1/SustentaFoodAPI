<?php

use App\Http\Controllers\Api\CardapioController;
use App\Http\Controllers\Api\ReservaController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\api\UserController;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Rotas Públicas
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/login', [TokenController::class, 'store']);
    Route::get('/reservas', [ReservaController::class, 'index']);
    Route::post('/reservas/notLoggedUser', [ReservaController::class, 'notLoggedUserStore']);
    Route::get('/cardapio', [CardapioController::class, 'index']);

// Rotas Privadas
Route::middleware(['auth:sanctum', 'verified'])->group(function() {
    Route::prefix('/users')->controller(UserController::class)->group(function() {
        // Route::get('/', 'index');
        Route::get('/{user}', 'show');
        Route::put('/{user}', 'update');
        Route::delete('/{user}', 'destroy');
    });

    Route::prefix('/reservas')->controller(ReservaController::class)->group(function() {
        // Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{reserva}', 'show')->middleware('checkReserva');
        Route::put('/{reserva}', 'update')->middleware('checkReserva');
        Route::delete('/{reserva}', 'destroy')->middleware('checkReserva');
    });

    Route::prefix('/cardapio')->controller(CardapioController::class)->middleware('checkRole')->group(function() {
        Route::post('/', 'store');
        Route::get('/{prato}', 'show');
        Route::put('/{prato}', 'update');
        Route::delete('/{prato}', 'destroy');
    });

    Route::delete('/logout', [TokenController::class, 'destroy']);
});

// Rota que o usuário acessa via link do e-mail (GET)
Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {
    $user = User::findOrFail($id);

    if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
        return response()->json(['message' => 'Verificação de e-mail falhou.'], 400);
    }

    $user->markEmailAsVerified();

    return redirect('http://127.0.0.1:8000/verify');
})->middleware(['signed'])->name('verification.verify');

// Rota para reenviar o link de verificação (POST)
Route::post('/email/verification-notification', function (Request $request) {

    if ($request->user()->hasVerifiedEmail()) {
        return response()->json(['message' => 'Seu e-mail já foi verificado.'], 400);
    }

    $request->user()->sendEmailVerificationNotification();

    return response()->json(['message' => 'Link de verificação enviado novamente!']);
})->name('verification.send');

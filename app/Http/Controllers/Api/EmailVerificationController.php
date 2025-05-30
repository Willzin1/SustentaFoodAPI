<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Reserva;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function confirmReserva($token)
    {
        $reserva = Reserva::where('confirmacao_token', $token)->first();

        $reserva->status = 'confirmada';
        $reserva->confirmacao_token = null;
        $reserva->save();

        return redirect('http://127.0.0.1:8000/confirmar-reserva');
    }

    public function verify($id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Verificação de e-mail falhou.'], 400);
        }

        $user->markEmailAsVerified();

        return redirect(env('APP_URL_FRONTEND') . '/verify');
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Seu e-mail já foi verificado.'], 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Link de verificação enviado novamente!']);
    }
} 
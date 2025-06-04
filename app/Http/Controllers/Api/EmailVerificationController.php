<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Reserva;
use Exception;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{

    public function confirmReserva($token)
    {
        try {
            $reserva = Reserva::where('confirmacao_token', $token)->first();

            if (!$reserva || $reserva->status === 'confirmada') {
                return redirect(env('APP_URL_FRONTEND') . '/confirmada-reserva');
            }

            $reserva->status = 'confirmada';
            $reserva->confirmacao_token = null;
            $reserva->save();

            return redirect(env('APP_URL_FRONTEND') . '/confirmar-reserva');
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro ao confirmar reserva',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verify($id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Verificação de e-mail falhou.'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect(env('APP_URL_FRONTEND') . '/confirmado-email?email=' . $user->email, 302);
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

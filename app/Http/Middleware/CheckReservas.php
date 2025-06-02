<?php

namespace App\Http\Middleware;

use App\Models\Reserva;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckReservas
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $reservaId = $request->route('reserva');
        $reserva = Reserva::find($reservaId);

        if (!$reserva) {
            return response()->json([
                'message' => 'Reserva não encontrada'
            ], 404);
        }

        if (Auth::user()->role == 'admin') {
            return $next($request);
        }

        if (Auth::id() !== $reserva->user_id) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        return $next($request);
    }
}

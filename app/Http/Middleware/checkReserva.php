<?php

namespace App\Http\Middleware;

use App\Models\Reserva;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class checkReserva
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

        if (Auth::id() !== $reserva->user_id) {
            return response()->json(['message' => 'Acesso não autorizado à reserva'], 403);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RelatorioReservaController extends Controller
{
    public function getReservationsByDay()
    {
        $total = Reserva::whereDate('data', Carbon::today())->count();
        return response()->json(['total' => $total]);
    }

    public function getReservationsByWeek()
    {
        $total = Reserva::whereBetween('data', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        return response()->json(['total' => $total]);
    }

    public function getReservationsByMonth()
    {
        $total = Reserva::whereBetween('data', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count();
        return response()->json(['total' => $total]);
    }

    public function getReservationsByWeekDay()
    {
        $data = Reserva::selectRaw('DATE(data) as dia, COUNT(*) as total')
            ->whereMonth('data', Carbon::now()->month)
            ->groupBy('dia')
            ->orderBy('dia')
            ->get()
            ->map(function ($item) {
                $item->label = Carbon::parse($item->dia)->format('d/m');
                return $item;
            });

        return response()->json($data);
    }
}

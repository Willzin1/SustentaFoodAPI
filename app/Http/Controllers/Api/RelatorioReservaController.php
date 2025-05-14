<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ReservasHelper;
use App\Http\Controllers\Controller;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Http\JsonResponse;

class RelatorioReservaController extends Controller
{
    public function getReservationsByDay(Request $request): JsonResponse
    {
        $query = Reserva::with('user')->whereDate('data', Carbon::today())->orderBy('data');

        $total = $query->count();

        if ($request->has('search')) {
        $search = $request->input('search');
        $filter = $request->input('filter');

        switch ($filter) {
            case 'ID':
                $query->where('id', 'like', "%$search%");
                break;
            case 'Nome':
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%");
                });
                break;
            case 'Data':
                $query->where('data', 'like', "%$search%");
                break;
            case 'Hora':
                $query->where('hora', 'like', "%$search%");
                break;
            case 'Quantidade':
                $query->where('quantidade_cadeiras', 'like', "%$search%");
                break;
            default:
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%$search%")
                        ->orWhere('data', 'like', "%$search%")
                        ->orWhere('hora', 'like', "%$search%")
                        ->orWhere('quantidade_cadeiras', 'like', "%$search%")
                        ->orWhereHas('user', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%$search%");
                        });
                });
        }
    }

        $todayReservations = $query->paginate(5, ['id', 'user_id', 'data', 'hora', 'quantidade_cadeiras', 'name', 'email']);

        return response()->json(['total' => $total, 'reservas' => $todayReservations]);
    }

    public function getReservationsByWeek(Request $request): JsonResponse
    {
        $startWeek = Carbon::now()->startOfWeek();
        $endWeek = Carbon::now()->endOfWeek();

        $query = Reserva::with('user')->whereBetween('data', [$startWeek, $endWeek])->orderBy('data');
        $total = $query->count();

        ReservasHelper::applySearchFilter($request, $query);

        $weekReservations = $query->paginate(5, ['id', 'user_id', 'data', 'hora', 'quantidade_cadeiras', 'name', 'email']);

        return response()->json(['total' => $total, 'reservas' => $weekReservations], 200);
    }

    public function getReservationsByMonth(Request $request): JsonResponse
    {
        $startMonth = Carbon::now()->startOfMonth();
        $endMonth = Carbon::now()->endOfMonth();

        $query = Reserva::with('user')->whereBetween('data', [$startMonth, $endMonth])->orderBy('data');
        $total = $query->count();

        ReservasHelper::applySearchFilter($request, $query);

        $monthReservations = $query->paginate(5, ['id', 'user_id', 'data', 'hora', 'quantidade_cadeiras', 'name', 'email']);

        return response()->json(['total' => $total, 'reservas' => $monthReservations], 200);
    }

    public function getReservationsByWeekDay(): JsonResponse
    {
        $data = Reserva::with('user')->selectRaw('DATE(data) as dia, COUNT(*) as total')
            ->whereMonth('data', Carbon::now()->month)
            ->groupBy('dia')
            ->orderBy('dia')
            ->get()
            ->map(function ($item) {
                $item->label = Carbon::parse($item->dia)->format('d/m');
                return $item;
            });

        return response()->json($data, 200);
    }
}

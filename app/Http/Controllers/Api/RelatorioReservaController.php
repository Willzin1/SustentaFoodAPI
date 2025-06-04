<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ReservasHelper;
use App\Http\Controllers\Controller;
use App\Models\Reserva;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller responsável por gerar relatórios de reservas
 */
class RelatorioReservaController extends Controller
{
    /**
     * Retorna relatório de reservas do dia atual
     *
     * @param Request $request Request com possíveis filtros de busca
     * @return JsonResponse Retorna uma resposta JSON contendo:
     * - total: Total de reservas do dia
     * - confirmadas: Número de reservas confirmadas
     * - pendentes: Número de reservas pendentes
     * - canceladas: Número de reservas canceladas
     * - reservas: Lista paginada (5 por página) com detalhes das reservas:
     *   - id
     *   - user_id
     *   - data
     *   - hora
     *   - quantidade_cadeiras
     *   - name
     *   - email
     *   - status
     *
     * Em caso de erro:
     * - message: Mensagem de erro
     * - error: Detalhes do erro
     */
    public function getReservationsByDay(Request $request): JsonResponse
    {
        try {

            $date = Carbon::today();

            $query = Reserva::with('user')->whereDate('data', $date)->orderBy('data');
            $confirmed = Reserva::whereDate('data', $date)->where('status', 'confirmada')->count();
            $pending = Reserva::whereDate('data', $date)->where('status', 'pendente')->count();
            $canceled = Reserva::whereDate('data', $date)->where('status', 'cancelada')->count();
            $total = $query->count();

            ReservasHelper::applySearchFilter($request, $query);

            $todayReservations = $query->paginate(5, ['id', 'user_id', 'data', 'hora', 'quantidade_cadeiras', 'name', 'email', 'status']);

            return response()->json([
                'total' => $total,
                'confirmadas' => $confirmed,
                'pendentes' => $pending,
                'canceladas' => $canceled,
                'reservas' => $todayReservations
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar reservas do dia',
                'error' => $e->getMessage()
        ], 500);
        }
    }

    /**
     * Retorna relatório de reservas da semana atual
     *
     * @param Request $request Request com possíveis filtros de busca
     * @return JsonResponse Retorna uma resposta JSON contendo:
     * - total: Total de reservas da semana
     * - confirmadas: Número de reservas confirmadas
     * - pendentes: Número de reservas pendentes
     * - canceladas: Número de reservas canceladas
     * - dias: Agrupamento de reservas por dia da semana
     * - reservas: Lista paginada (5 por página) com detalhes das reservas
     *
     * Em caso de erro:
     * - message: Mensagem de erro
     * - error: Detalhes do erro
     */
    public function getReservationsByWeek(Request $request): JsonResponse
    {
        try {
            $startWeek = Carbon::now()->startOfWeek();
            $endWeek = Carbon::now()->endOfWeek();

            $query = Reserva::with('user')->whereBetween('data', [$startWeek, $endWeek])->orderBy('data');
            $confirmed = Reserva::whereBetween('data', [$startWeek, $endWeek])->where('status', 'confirmada')->count();
            $pending = Reserva::whereBetween('data', [$startWeek, $endWeek])->where('status', 'pendente')->count();
            $canceled = Reserva::whereBetween('data', [$startWeek, $endWeek])->where('status', 'cancelada')->count();
            $total = $query->count();

            ReservasHelper::applySearchFilter($request, $query);
            $days = ReservasHelper::getWeekdayReservations($query->get());

            $weekReservations = $query->paginate(5, ['id', 'user_id', 'data', 'hora', 'quantidade_cadeiras', 'name', 'email', 'status']);

            return response()->json([
                'total' => $total,
                'confirmadas' => $confirmed,
                'pendentes' => $pending,
                'canceladas' => $canceled,
                'dias' => $days,
                'reservas' => $weekReservations
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar reservas da semana',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    /**
     * Retorna relatório de reservas do mês atual
     *
     * @param Request $request Request com possíveis filtros de busca
     * @return JsonResponse Retorna uma resposta JSON contendo:
     * - total: Total de reservas do mês
     * - confirmadas: Número de reservas confirmadas
     * - pendentes: Número de reservas pendentes
     * - canceladas: Número de reservas canceladas
     * - semanas: Agrupamento de reservas por semana do mês
     * - reservas: Lista paginada (5 por página) com detalhes das reservas
     *
     * Em caso de erro:
     * - message: Mensagem de erro
     * - error: Detalhes do erro
     */
    public function getReservationsByMonth(Request $request): JsonResponse
    {
        $startMonth = Carbon::now()->startOfMonth();
        $endMonth = Carbon::now()->endOfMonth();

        $query = Reserva::with('user')->whereBetween('data', [$startMonth, $endMonth])->orderBy('data');
        $confirmed = Reserva::whereBetween('data', [$startMonth, $endMonth])->where('status', 'confirmada')->count();
        $pending = Reserva::whereBetween('data', [$startMonth, $endMonth])->where('status', 'pendente')->count();
        $canceled = Reserva::whereBetween('data', [$startMonth, $endMonth])->where('status', 'cancelada')->count();
        $total = $query->count();

        ReservasHelper::applySearchFilter($request, $query);
        $week = ReservasHelper::getWeekReservations($query->get());

        $monthReservations = $query->paginate(5, ['id', 'user_id', 'data', 'hora', 'quantidade_cadeiras', 'name', 'email', 'status']);

        return response()->json([
            'total' => $total,
            'confirmadas' => $confirmed,
            'pendentes' => $pending,
            'canceladas' => $canceled,
            'semanas' => $week,
            'reservas' => $monthReservations
        ], 200);
    }
}

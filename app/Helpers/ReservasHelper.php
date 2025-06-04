<?php

namespace App\Helpers;

use App\Models\Reserva;
use Carbon\Carbon;

/**
 * Helper class para gerenciamento de reservas
 * Fornece métodos utilitários para validação e análise de reservas
 */
class ReservasHelper
{
    /**
     * Verifica disponibilidade de lugares para uma reserva
     *
     * @param string $data Data da reserva
     * @param string $hora Hora da reserva
     * @param string $quantidade Quantidade de cadeiras solicitadas
     * @return bool True se há disponibilidade, False caso contrário
     *
     * Capacidade máxima: 80 lugares
     */
    public static function checkAvailability(string $data, string $hora, string $quantidade): bool
    {
        $isValid = true;
        $maxCap = 80;
        $chairsOccupied = Reserva::where('data', $data)
            ->where('hora', $hora)
            ->sum('quantidade_cadeiras');

        $chairsAvailable = $maxCap - $chairsOccupied;

        if ($quantidade > $chairsAvailable) {
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * Verifica se usuário atingiu limite de reservas
     *
     * @param string $user_id ID do usuário
     * @return bool True se não atingiu limite, False caso contrário
     *
     * Limite máximo: 4 reservas por usuário
     */
    public static function checkReservationLimit(string $user_id): bool
    {
        $isValid = true;
        $maxReservations = 4;
        $userReservations = Reserva::where('user_id', $user_id)->count();

        if ($userReservations >= $maxReservations) {
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * Aplica filtros de busca na query de reservas
     *
     * @param Request $request Request com parâmetros de busca
     * @param Builder $query Query builder das reservas
     * @return Builder Query modificada com filtros
     *
     * Filtros disponíveis:
     * - ID
     * - Nome
     * - Data
     * - Hora
     * - Quantidade
     */
    public static function applySearchFilter($request, $query)
    {
        if ($request->has('search')) {
            $search = $request->input('search');
            $filter = $request->input('filter');

            switch ($filter) {
                case 'ID':
                    $query->where('id', 'like', "%$search%");
                    break;
                case 'Nome':
                    $query->where('name', 'like', "%$search%");
                    break;
                case 'Data':
                    $query->whereDate('data', $search);
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
                            ->orWhereDate('data', $search)
                            ->orWhere('hora', 'like', "%$search%")
                            ->orWhere('quantidade_cadeiras', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    });
            }
        }

        return $query;
    }

    /**
     * Agrupa reservas por dia da semana
     *
     * @param Collection $reservas Collection de reservas
     * @return array Array associativo com contagem por dia:
     * - Segunda
     * - Terça
     * - Quarta
     * - Quinta
     * - Sexta
     * - Sábado
     * - Domingo
     */
    public static function getWeekdayReservations($reservas)
    {
        $daysOfWeek = [
            'Segunda' => 0,
            'Terça' => 0,
            'Quarta' => 0,
            'Quinta' => 0,
            'Sexta' => 0,
            'Sábado' => 0,
            'Domingo' => 0,
        ];

        foreach ($reservas as $reserva) {
            $dia = Carbon::parse($reserva->data)->locale('pt_BR')->isoFormat('dddd');

            $diaFormatado = ucfirst(str_replace('-feira', '', $dia));

            if (isset($daysOfWeek[$diaFormatado])) {
                $daysOfWeek[$diaFormatado]++;
            }
        }

        return $daysOfWeek;
    }

    /**
     * Agrupa reservas por semana do mês
     *
     * @param Collection $reservas Collection de reservas
     * @return array Array com dados das semanas:
     * - semana: Número da semana (1 a 5)
     * - total: Total de reservas na semana
     * - periodo: Período da semana (dd/mm a dd/mm)
     *
     * Características:
     * - Calcula semanas com base no dia do mês
     * - Ajusta fim de semana ao fim do mês
     * - Formata período com datas
     */
    public static function getWeekReservations($reservas)
    {
    $semanas = [];

    foreach ($reservas as $reserva) {
        $data = Carbon::parse($reserva->data);

        $dia = $data->day;
        $mes = $data->month;
        $ano = $data->year;

        // Semana do mês (1 a 5)
        $semanaDoMes = ceil($dia / 7);
        $chave = "Semana $semanaDoMes";

        // Calcula início e fim da semana com base no número
        $inicioSemana = Carbon::create($ano, $mes, 1)->addDays(($semanaDoMes - 1) * 7);
        $fimSemana = (clone $inicioSemana)->addDays(6);

        // Garante que o fim da semana não ultrapasse o fim do mês
        $fimDoMes = Carbon::create($ano, $mes)->endOfMonth();
        if ($fimSemana->gt($fimDoMes)) {
            $fimSemana = $fimDoMes;
        }

        // Formata o período da semana
        $periodo = $inicioSemana->format('d/m') . ' a ' . $fimSemana->format('d/m');

        if (!isset($semanas[$chave])) {
            $semanas[$chave] = [
                'semana' => $chave,
                'total' => 0,
                'periodo' => $periodo
            ];
        }

        $semanas[$chave]['total']++;
    }

        // Retorna como array de valores (sem as chaves associativas)
        return array_values($semanas);
    }
}

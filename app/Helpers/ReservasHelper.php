<?php

namespace App\Helpers;

use App\Models\Reserva;
use Carbon\Carbon;

class ReservasHelper
{
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
                    $query->whereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    });
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
                            ->orWhereHas('user', function ($q2) use ($search) {
                                $q2->where('name', 'like', "%$search%");
                            });
                    });
            }
        }

        return $query;
    }

    public static function confirmReservation($token)
    {
        $reserva = Reserva::where('confirmacao_token', $token)->first();

        $reserva->status = 'confirmada';
        $reserva->confirmacao_token = null;
        $reserva->save();

        return view('emails.reservation_confirmed2');
    }

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

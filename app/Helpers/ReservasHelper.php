<?php

namespace App\Helpers;

use App\Models\Reserva;

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
            $search = $request('search');
            $filter = $request('filter');

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
        return $query;
    }
}

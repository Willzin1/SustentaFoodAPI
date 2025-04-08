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
}

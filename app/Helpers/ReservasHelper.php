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
        $search = $request->input('search');
        $filter = $request->input('filter');

            match ($filter) {
                'ID' => $query->where('id', 'like', "%$search%"),
                'Nome' => $query->whereHas('user', fn($q) => $q->where('name', 'like', "%$search%")),
                'Data' => $query->where('data', 'like', "%$search%"),
                'Hora' => $query->where('hora', 'like', "%$search%"),
                'Quantidade' => $query->where('quantidade_cadeiras', 'like', "%$search%"),
                default => $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%$search%")
                    ->orWhere('data', 'like', "%$search%")
                    ->orWhere('hora', 'like', "%$search%")
                    ->orWhere('quantidade_cadeiras', 'like', "%$search%")
                    ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%$search%"));
                }),
            };

            return $query;
        }
    }
}

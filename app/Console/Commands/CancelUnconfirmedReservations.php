<?php

namespace App\Console\Commands;

use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CancelUnconfirmedReservations extends Command
{
    protected $signature = 'reservas:cancel-unconfirmed';
    protected $description = 'Cancel unconfirmed reservations after X hours';

    public function handle()
    {
        // Configurar o limite de tempo (ex: 24 horas)
        $hoursLimit = 1;
        
        $cutoffTime = Carbon::now()->subHours($hoursLimit);

        // Buscar e cancelar reservas não confirmadas
        $canceledCount = Reserva::where('status', 'pendente')
            ->where('created_at', '<=', $cutoffTime)
            ->update(['status' => 'cancelada']);

        $this->info("Foram canceladas {$canceledCount} reservas não confirmadas.");
    }
}
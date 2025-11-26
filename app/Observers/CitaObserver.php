<?php

namespace App\Observers;

use App\Models\Cita;
use App\Services\CitaNotificationService;

class CitaObserver
{
    public function updated(Cita $cita): void
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        if (! $this->estatusConfirmadoRecien($cita)) {
            return;
        }

        CitaNotificationService::enviarConfirmacion($cita);
        CitaNotificationService::programarRecordatorio($cita);
    }

    private function estatusConfirmadoRecien(Cita $cita): bool
    {
        if (! $cita->wasChanged('estatus')) {
            return false;
        }

        $estatusAnterior = $cita->getOriginal('estatus');

        return $cita->estatus === 'confirmada' && $estatusAnterior !== 'confirmada';
    }
}

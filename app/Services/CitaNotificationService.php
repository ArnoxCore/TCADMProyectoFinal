<?php

namespace App\Services;

use App\Models\Cita;
use App\Notifications\CitaConfirmadaNotification;
use App\Notifications\CitaRecordatorioNotification;
use Carbon\Carbon;

class CitaNotificationService
{
    public static function enviarConfirmacion(Cita $cita): void
    {
        $cita->loadMissing('cliente.user', 'vehiculo', 'servicios');

        $clienteUser = optional($cita->cliente)->user;

        if (! $clienteUser || ! $clienteUser->email) {
            return;
        }

        $clienteUser->notify(new CitaConfirmadaNotification($cita));
    }

    public static function programarRecordatorio(Cita $cita): void
    {
        $cita->loadMissing('cliente.user');

        $clienteUser = optional($cita->cliente)->user;
        if (! $clienteUser || ! $clienteUser->email) {
            return;
        }

        if (! $cita->fecha || ! $cita->hora_inicio) {
            return;
        }

        $fecha = $cita->fecha instanceof Carbon
            ? $cita->fecha->format('Y-m-d')
            : (string) $cita->fecha;

        $inicioProgramado = Carbon::parse(
            $fecha.' '.$cita->hora_inicio,
            Cita::LOCAL_TIMEZONE
        )->setTimezone(config('app.timezone'));

        $envioRecordatorio = $inicioProgramado->copy()->subDay();
        $notification = new CitaRecordatorioNotification($cita);

        if ($envioRecordatorio->isPast()) {
            $clienteUser->notify($notification);
            return;
        }

        $clienteUser->notify($notification->delay($envioRecordatorio));
    }
}

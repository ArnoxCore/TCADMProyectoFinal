<?php

namespace App\Notifications;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class CitaConfirmadaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Datos resumidos de la cita para el correo.
     */
    private array $payload;

    public function __construct(Cita $cita)
    {
        $this->onQueue('mail');

        $services = $cita->servicios->pluck('nombre')->filter()->implode(', ');
        $scheduledAt = $this->parseFechaHora($cita);
        $vehiculo = $cita->vehiculo;
        $vehiculoTexto = $vehiculo
            ? trim(($vehiculo->marca ?? '') . ' ' . ($vehiculo->modelo ?? '') . ' ' . ($vehiculo->placa ? '(' . $vehiculo->placa . ')' : ''))
            : null;

        $this->payload = [
            'folio' => $cita->id,
            'cliente' => optional(optional($cita->cliente)->user)->name,
            'fecha' => $scheduledAt?->format('d/m/Y'),
            'hora' => $scheduledAt?->format('H:i'),
            'servicios' => $services ?: 'Servicio general',
            'vehiculo' => $vehiculoTexto ?: 'Vehículo no registrado',
        ];
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu cita ha sido confirmada')
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('Tu cita fue confirmada exitosamente. Aquí tienes los detalles:')
            ->line('Fecha: ' . ($this->payload['fecha'] ?? 'Por definir'))
            ->line('Hora: ' . ($this->payload['hora'] ?? 'Por definir'))
            ->line('Servicios: ' . $this->payload['servicios'])
            ->line('Vehículo: ' . $this->payload['vehiculo'])
            ->line('Folio: #' . $this->payload['folio'])
            ->line('Te contactaremos si se requiere información adicional. ¡Gracias por confiar en nosotros!');
    }

    private function parseFechaHora(Cita $cita): ?Carbon
    {
        if (! $cita->fecha || ! $cita->hora_inicio) {
            return null;
        }

        $fecha = $cita->fecha instanceof Carbon
            ? $cita->fecha->format('Y-m-d')
            : (string) $cita->fecha;

        return Carbon::parse($fecha . ' ' . $cita->hora_inicio, Cita::LOCAL_TIMEZONE)
            ->setTimezone(config('app.timezone'));
    }
}

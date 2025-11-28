<?php

namespace App\Http\Controllers\Recepcion;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Mecanico;
use App\Services\CitaNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RecepcionDashboardController extends Controller
{
    private const LOCAL_TZ = 'America/Mexico_City';

    public function index(Request $request)
    {
        // ¿Quiere ver todas las citas sin filtrar por fecha?
        $showAll = $request->boolean('show_all');

        // Fecha que se está viendo (puede venir null)
        $fecha = $request->input('fecha');

        // Si NO pidió "ver todas" y NO mandó fecha, usamos hoy
        if (! $showAll && ! $fecha) {
            $fecha = Carbon::now(self::LOCAL_TZ)->toDateString();
        }

        $citasQuery = Cita::with([
            'cliente.user',
            'vehiculo',
            'servicios',
            'mecanico.user'
        ]);

        // Solo filtramos por fecha si NO está en modo "ver todas"
        if (! $showAll && $fecha) {
            $citasQuery->whereDate('fecha', $fecha);
        }

        // Paginación (5 por página)
        $citas = (clone $citasQuery)
            ->orderBy('hora_inicio')
            ->paginate(5);

        // KPIs (según filtros del servidor)
        $statsBase = clone $citasQuery;

        $stats = [
            'total'     => (clone $statsBase)->count(),
            'pending'   => (clone $statsBase)->where('estatus', 'pendiente')->count(),
            'completed' => (clone $statsBase)->where('estatus', 'completada')->count(),
            'filtered'  => $citas->total(), // total según filtros (para esa vista)
        ];

        // Mecánicos activos para el combo de filtros
        $mecanicos = Mecanico::with('user')
            ->where('activo', true)
            ->get();

        return view('recepcion.dashboard', compact('citas', 'mecanicos', 'fecha', 'stats', 'showAll'));
    }

    // ========= AJAX: listado de citas (para el filtro de fecha) =========
    public function citasPorFecha(Request $request)
    {
        $fecha = $request->input('fecha');

        $query = Cita::with([
            'cliente.user',
            'vehiculo',
            'servicios',
            'mecanico.user'
        ]);

        if (!empty($fecha)) {
            $query->whereDate('fecha', $fecha);
        }

        $citas = $query
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        $data = $citas->map(function ($cita) {
            $vehiculo = $cita->vehiculo
                ? trim(($cita->vehiculo->marca ?? '') . ' ' . ($cita->vehiculo->modelo ?? '') . ' ' . ($cita->vehiculo->anio ?? ''))
                : '—';

            $servicios = $cita->servicios
                ? $cita->servicios->pluck('nombre')->implode(', ')
                : null;

            $estatus = $cita->estatus;

            return [
                'id'       => $cita->id,
                'fecha'    => $cita->fecha?->format('Y-m-d'),
                'hora'     => $cita->hora_inicio ? Carbon::parse($cita->hora_inicio)->format('H:i') : null,
                'cliente'  => $cita->cliente->user->name ?? '—',
                'vehiculo' => $vehiculo ?: '—',
                'servicio' => $servicios ?: '—',
                'mecanico' => optional(optional($cita->mecanico)->user)->name ?? 'Sin asignar',
                'estatus'  => [
                    'value' => $estatus,
                    'label' => $cita->estatus_texto ?? ucfirst(str_replace('_', ' ', $estatus)),
                ],
                'attendance' => [
                    'label'     => $cita->attendance_label,
                    'secondary' => $cita->attendance_secondary,
                    'check_in'  => $cita->formattedCheckIn(),
                    'inicio'    => $cita->formattedInicioReal(),
                    'asistio'   => $cita->asistio,
                ],
                'actions' => $this->actionsPayload($cita),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    // ========= AJAX: registrar check-in =========
    public function registrarCheckIn(Request $request, Cita $cita)
    {
        if (! $cita->canCheckIn()) {
            return $this->attendanceError('Esta cita ya cuenta con registro de asistencia o fue cancelada.');
        }

        $cita->check_in_at = Carbon::now(self::LOCAL_TZ);
        $cita->asistio = true;
        $cita->save();

        return $this->attendanceSuccess($cita, 'Asistencia registrada correctamente.');
    }

    // ========= AJAX: registrar inicio del servicio =========
    public function iniciarServicio(Request $request, Cita $cita)
    {
        if (! $cita->canStartService()) {
            return $this->attendanceError('Registra primero la llegada del cliente para poder iniciar el servicio.');
        }

        $cita->inicio_real_at = Carbon::now(self::LOCAL_TZ);

        if (in_array($cita->estatus, ['pendiente', 'confirmada'])) {
            $cita->estatus = 'en_proceso';
        }

        $cita->save();

        return $this->attendanceSuccess($cita, 'Inicio del servicio registrado.');
    }

    // ========= AJAX: marcar inasistencia =========
    public function marcarNoShow(Request $request, Cita $cita)
    {
        if (! $cita->canMarkNoShow()) {
            return $this->attendanceError('No es posible marcar inasistencia para esta cita.');
        }

        $cita->asistio = false;
        $cita->check_in_at = null;
        $cita->inicio_real_at = null;
        $cita->estatus = 'cancelada';
        $cita->save();

        return $this->attendanceSuccess($cita, 'La cita se marcó como inasistencia.');
    }

    // ========= AJAX: cancelar cita desde recepción =========
    public function cancelar(Request $request, Cita $cita)
    {
        if (! $cita->canCancelDesdeRecepcion()) {
            return $this->attendanceError('Este estado ya no permite cancelar la cita.');
        }

        $cita->estatus = 'cancelada';
        $cita->check_in_at = null;
        $cita->inicio_real_at = null;
        $cita->asistio = null;
        $cita->save();

        CitaNotificationService::notificarCancelacionPorRecepcion($cita);

        return $this->attendanceSuccess($cita, 'La cita fue cancelada desde recepción.');
    }


    public function citasCalendario(Request $request)
    {
            $startParam = $request->input('start');
            $endParam = $request->input('end');

            if (! $startParam || ! $endParam) {
                return response()->json([
                    'success' => false,
                    'message' => 'El calendario requiere un rango de fechas válido.',
                ], 422);
            }

            try {
                $start = Carbon::parse($startParam)->startOfDay();
                $end = Carbon::parse($endParam)->endOfDay();
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rango de fechas inválido para el calendario.',
                ], 422);
            }

            $query = Cita::with(['cliente.user', 'vehiculo', 'servicios', 'mecanico.user'])
                ->whereBetween('fecha', [$start->toDateString(), $end->toDateString()]);

            if ($request->filled('estatus')) {
                $query->where('estatus', $request->input('estatus'));
            }

            if ($request->filled('mecanico')) {
                $query->whereHas('mecanico.user', function ($q) use ($request) {
                    $q->where('name', $request->input('mecanico'));
                });
            }

            if ($request->filled('cliente')) {
                $clienteBusqueda = $request->input('cliente');
                $query->whereHas('cliente.user', function ($q) use ($clienteBusqueda) {
                    $q->where('name', 'like', '%'.$clienteBusqueda.'%');
                });
            }

            $statusColors = [
                'pendiente'   => '#facc15',
                'confirmada'  => '#38bdf8',
                'en_proceso'  => '#818cf8',
                'completada'  => '#34d399',
                'cancelada'   => '#f87171',
            ];

            $events = $query
                ->orderBy('fecha')
                ->orderBy('hora_inicio')
                ->get()
                ->map(function (Cita $cita) use ($statusColors) {
                    $fecha = $cita->fecha instanceof Carbon
                        ? $cita->fecha->format('Y-m-d')
                        : (string) $cita->fecha;

                    $inicio = Carbon::parse($fecha.' '.($cita->hora_inicio ?? '08:00:00'), Cita::LOCAL_TIMEZONE)
                        ->setTimezone(config('app.timezone'));

                    $finBase = $cita->hora_fin ?? $cita->hora_inicio;
                    $fin = Carbon::parse($fecha.' '.($finBase ?? '09:00:00'), Cita::LOCAL_TIMEZONE)
                        ->setTimezone(config('app.timezone'));

                    if ($fin->lessThanOrEqualTo($inicio)) {
                        $fin = $inicio->copy()->addHour();
                    }

                    $servicios = $cita->servicios->pluck('nombre')->filter()->implode(', ');
                    $attendanceLabel = $cita->attendance_label;

                    if ($cita->estatus === 'cancelada') {
                        $attendanceLabel = 'No aplica (cancelada)';
                    }

                    return [
                        'id' => $cita->id,
                        'title' => optional(optional($cita->cliente)->user)->name ?? 'Cliente sin nombre',
                        'start' => $inicio->toIso8601String(),
                        'end' => $fin->toIso8601String(),
                        'backgroundColor' => $statusColors[$cita->estatus] ?? '#94a3b8',
                        'borderColor' => $statusColors[$cita->estatus] ?? '#94a3b8',
                        'textColor' => '#0f172a',
                        'extendedProps' => [
                            'folio' => $cita->id,
                            'estatus' => $cita->estatus_texto ?? ucfirst($cita->estatus),
                            'cliente' => optional(optional($cita->cliente)->user)->name ?? '—',
                            'mecanico' => optional(optional($cita->mecanico)->user)->name ?? 'Sin asignar',
                            'servicios' => $servicios ?: '—',
                            'vehiculo' => $cita->vehiculo
                                ? trim(($cita->vehiculo->marca ?? '').' '.($cita->vehiculo->modelo ?? ''))
                                : '—',
                            'asistencia' => $attendanceLabel,
                        ],
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $events,
            ]);
    }

    private function attendanceSuccess(Cita $cita, string $message)
    {
        return response()->json([
            'success'    => true,
            'message'    => $message,
            'estatus'    => $this->estatusPayload($cita),
            'attendance' => $this->attendancePayload($cita),
            'actions'    => $this->actionsPayload($cita),
        ]);
    }

    private function attendanceError(string $message, int $status = 422)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    private function estatusPayload(Cita $cita): array
    {
        $estatus = $cita->estatus;

        return [
            'value' => $estatus,
            'label' => $cita->estatus_texto ?? ucfirst(str_replace('_', ' ', $estatus)),
        ];
    }

    private function attendancePayload(Cita $cita): array
    {
        return [
            'label'          => $cita->attendance_label,
            'secondary'      => $cita->attendance_secondary,
            'asistio'        => $cita->asistio,
            'check_in_at'    => $cita->formattedCheckIn(),
            'inicio_real_at' => $cita->formattedInicioReal(),
        ];
    }

    private function actionsPayload(Cita $cita): array
    {
        return [
            'can_check_in'    => $cita->canCheckIn(),
            'can_start'       => $cita->canStartService(),
            'can_mark_no_show'=> $cita->canMarkNoShow(),
            'can_cancel'      => $cita->canCancelDesdeRecepcion(),
        ];
    }
}

<?php

namespace App\Http\Controllers\Recepcion;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Mecanico;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RecepcionDashboardController extends Controller
{
    // 🔹 Límite y estatus que cuentan como "ocupación" del mecánico
    private const MAX_CITAS_POR_DIA = 4;

    private const ESTATUS_OCUPACION = [
        'pendiente',
        'confirmada',
        'en_proceso',
        'completada',
    ];

    public function index(Request $request)
    {
        // ¿Quiere ver todas las citas sin filtrar por fecha?
        $showAll = $request->boolean('show_all');

        // Fecha que se está viendo (puede venir null)
        $fecha = $request->input('fecha');

        // Si NO pidió "ver todas" y NO mandó fecha, usamos hoy
        if (! $showAll && ! $fecha) {
            $fecha = Carbon::now('America/Mexico_City')->toDateString();
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
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    // ========= AJAX: mecánicos disponibles para una cita =========
    public function mecanicosDisponibles(Cita $cita)
    {
        $fecha = $cita->fecha;

        $mecanicos = Mecanico::with('user')
            ->where('activo', true)
            ->withCount(['citas as citas_vigentes' => function ($q) use ($fecha) {
                $q->whereDate('fecha', $fecha)
                    ->whereIn('estatus', self::ESTATUS_OCUPACION);
            }])
            ->having('citas_vigentes', '<', self::MAX_CITAS_POR_DIA)
            ->get();

        $data = $mecanicos->map(function ($mecanico) {
            return [
                'id'             => $mecanico->id,
                'nombre'         => $mecanico->user?->name,
                'citas_vigentes' => $mecanico->citas_vigentes,
                'max_citas'      => self::MAX_CITAS_POR_DIA,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    // ========= AJAX: asignar mecánico + confirmar cita =========
    public function asignarYConfirmar(Request $request, Cita $cita)
    {
        $request->validate([
            'mecanico_id' => 'required|exists:mecanicos,id',
        ]);

        $mecanicoId = (int) $request->input('mecanico_id');
        $fecha      = $cita->fecha;

        // Contar citas del mecánico ese día (ocupación)
        $citasDelDia = Cita::where('mecanico_id', $mecanicoId)
            ->whereDate('fecha', $fecha)
            ->whereIn('estatus', self::ESTATUS_OCUPACION)
            // si la cita ya tenía ese mecánico, no la contamos doble
            ->when($cita->mecanico_id === $mecanicoId, function ($q) use ($cita) {
                $q->where('id', '!=', $cita->id);
            })
            ->count();

        if ($citasDelDia >= self::MAX_CITAS_POR_DIA) {
            return response()->json([
                'success' => false,
                'message' => 'Este mecánico ya no tiene disponibilidad para esa fecha.',
            ], 422);
        }

        // Asignar mecánico y confirmar
        $cita->mecanico_id = $mecanicoId;
        $cita->estatus     = 'confirmada';
        $cita->save();

        $estatus = $cita->estatus;
        $label   = $cita->estatus_texto ?? ucfirst(str_replace('_', ' ', $estatus));

        return response()->json([
            'success' => true,
            'message' => 'Cita confirmada y mecánico asignado correctamente.',
            'estatus' => [
                'value' => $estatus,
                'label' => $label,
            ],
            'mecanico' => $cita->mecanico?->user?->name ?? 'Sin asignar',
        ]);
    }

    // ========= AJAX: cancelar cita desde recepción =========
    public function cancelar(Request $request, Cita $cita)
    {
        // Luego si quieres bloqueamos por fecha, por ahora libre
        $cita->estatus = 'cancelada';
        $cita->save();

        $estatus = $cita->estatus;
        $label   = $cita->estatus_texto ?? ucfirst(str_replace('_', ' ', $estatus));

        return response()->json([
            'success' => true,
            'message' => 'La cita fue cancelada desde recepción.',
            'estatus' => [
                'value' => $estatus,
                'label' => $label,
            ],
        ]);
    }
}

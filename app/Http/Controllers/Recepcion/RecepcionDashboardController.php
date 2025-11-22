<?php

namespace App\Http\Controllers\Recepcion;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Mecanico;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RecepcionDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Fecha que se está viendo (si no viene, usamos HOY en zona de México)
        $fecha = $request->input('fecha');

        if (!$fecha) {
            $fecha = Carbon::now('America/Mexico_City')->toDateString();
        }

        // Citas de ese día con todas las relaciones necesarias
        $citasQuery = Cita::with([
            'cliente.user',
            'vehiculo',
            'servicios',       // <- aquí va la relación MANY-TO-MANY real
            'mecanico.user'
        ])
            ->whereDate('fecha', $fecha);

        $citas = (clone $citasQuery)
            ->orderBy('hora_inicio')
            ->get();

        // KPIs (por fecha seleccionada)
        $stats = [
            'total'     => (clone $citasQuery)->count(),
            'pending'   => (clone $citasQuery)->where('estatus', 'pendiente')->count(),
            'completed' => (clone $citasQuery)->where('estatus', 'completada')->count(),
            'filtered'  => (clone $citasQuery)->count(), // el front luego lo ajusta
        ];

        // Mecánicos activos para el combo
        $mecanicos = Mecanico::with('user')
            ->where('activo', true)
            ->get();

        return view('recepcion.dashboard', compact('citas', 'mecanicos', 'fecha', 'stats'));
    }

    // Endpoint JSON para AJAX (fecha opcional)
    public function citasPorFecha(Request $request)
    {
        $fecha = $request->input('fecha'); // puede venir null / "" / "2025-11-25"

        $query = Cita::with([
            'cliente.user',
            'vehiculo',
            'servicios',
            'mecanico.user'
        ]);

        // Si hay fecha, filtramos; si viene vacía, traemos TODAS
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
                'fecha'    => $cita->fecha?->format('Y-m-d'),
                'hora'     => $cita->hora_inicio ? Carbon::parse($cita->hora_inicio)->format('H:i') : null,
                'cliente'  => $cita->cliente->user->name ?? '—',
                'vehiculo' => $vehiculo ?: '—',
                'servicio' => $servicios ?: '—',
                'mecanico' => optional(optional($cita->mecanico)->user)->name ?? 'Sin asignar',
                'estatus'  => [
                    'value' => $estatus, // pendiente / confirmada / ...
                    'label' => $cita->estatus_texto ?? ucfirst(str_replace('_', ' ', $estatus)),
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}

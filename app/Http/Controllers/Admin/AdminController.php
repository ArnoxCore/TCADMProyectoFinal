<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Servicio;
use App\Models\Cita;
use App\Models\Mecanico;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    private const ESTATUS_ACTIVOS = ['pendiente', 'confirmada', 'en_proceso'];

    /**
     * Display the administrator dashboard panel.
     */
    public function dashboard(Request $request)
    {
        $this->ensureMechanicProfilesExist();

        $totalCitas = Cita::count();
        $completadas = Cita::where('estatus', 'completada')->count();
        $porConfirmar = Cita::where('estatus', 'pendiente')->count();

        $tasaCompletadas = $totalCitas > 0
            ? round(($completadas / $totalCitas) * 100, 1)
            : 0;

        $citasPendientes = Cita::with([
                'cliente.user:id,name',
                'vehiculo:id,marca,modelo,placa',
                'servicios:id,nombre',
                'mecanico.user:id,name'
            ])
            ->where('estatus', 'pendiente')
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->simplePaginate(6);

        $mecanicosDisponibles = Mecanico::with('user:id,name')
            ->where('activo', true)
            ->withCount(['citas as citas_activas_count' => function ($query) {
                $query->whereNull('deleted_at')
                    ->whereIn('estatus', self::ESTATUS_ACTIVOS);
            }])
            ->orderBy('numero_empleado')
            ->get()
            ->filter(fn ($mecanico) => ($mecanico->citas_activas_count ?? 0) < 4)
            ->values();

        return view('Panel-admin.Index', [
            'citasPendientes' => $citasPendientes,
            'mecanicosDisponibles' => $mecanicosDisponibles,
            'totalCitas' => $totalCitas,
            'tasaCompletadas' => $tasaCompletadas,
            'porAsignar' => $porConfirmar,
        ]);
    }

    /**
     * Show services management panel.
     */
    public function servicios(Request $request)
    {
        $servicios = Servicio::orderBy('nombre')->simplePaginate(5);

        return view('Panel-admin.servicios', compact('servicios'));
    }

    /**
     * Store a new service created from the admin panel modal.
     */
    public function storeServicio(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'duracion_estimada' => 'required|integer|min:1',
            'precio_base' => 'required|numeric|min:0',
        ]);

        Servicio::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'duracion_estimada' => $validated['duracion_estimada'],
            'precio_base' => $validated['precio_base'],
            'activo' => true,
        ]);

        return redirect()->route('admin.servicios')
            ->with('success', 'Servicio creado correctamente.');
    }

    /**
     * Update an existing service.
     */
    public function updateServicio(Request $request, Servicio $servicio)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'duracion_estimada' => 'required|integer|min:1',
            'precio_base' => 'required|numeric|min:0',
        ]);

        $servicio->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'duracion_estimada' => $validated['duracion_estimada'],
            'precio_base' => $validated['precio_base'],
        ]);

        return redirect()->route('admin.servicios')
            ->with('success', 'Servicio actualizado correctamente.');
    }

    /**
     * Delete a service from catalog.
     */
    public function destroyServicio(Servicio $servicio)
    {
        $nombre = $servicio->nombre;
        $servicio->delete();

        return redirect()->route('admin.servicios')
            ->with('success', "Servicio '{$nombre}' eliminado correctamente.");
    }

    /**
     * Show reports panel.
     */
    public function reportes(Request $request)
    {
        return view('Panel-admin.reportes');
    }

    /**
     * Show statistics dashboard panel.
     */
    public function estadisticas(Request $request)
    {
        $statusOrder = [
            'pendiente' => 'Pendiente',
            'confirmada' => 'Confirmada',
            'en_proceso' => 'En proceso',
            'completada' => 'Completada',
            'cancelada' => 'Cancelada',
        ];

        $rangeStart = $request->filled('desde')
            ? Carbon::parse($request->input('desde'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $rangeEnd = $request->filled('hasta')
            ? Carbon::parse($request->input('hasta'))->endOfDay()
            : Carbon::now()->endOfMonth();

        if ($rangeStart->greaterThan($rangeEnd)) {
            [$rangeStart, $rangeEnd] = [$rangeEnd, $rangeStart];
        }

        $gracia = (int) $request->input('tolerancia', 10);
        $gracia = $gracia > 0 ? $gracia : 10;

        $baseQuery = Cita::whereBetween('fecha', [$rangeStart->toDateString(), $rangeEnd->toDateString()]);

        $totalRango = (clone $baseQuery)->count();

        $statusCounts = (clone $baseQuery)
            ->selectRaw('estatus, COUNT(*) as total')
            ->groupBy('estatus')
            ->pluck('total', 'estatus');

        $chartData = [
            'labels' => array_values($statusOrder),
            'totals' => array_map(fn ($key) => (int) ($statusCounts[$key] ?? 0), array_keys($statusOrder)),
        ];

        $resumenPeriodo = [
            'total' => $totalRango,
            'completadas' => (int) ($statusCounts['completada'] ?? 0),
            'pendientes' => (int) ($statusCounts['pendiente'] ?? 0),
            'confirmadas' => (int) ($statusCounts['confirmada'] ?? 0),
            'canceladas' => (int) ($statusCounts['cancelada'] ?? 0),
        ];

        $attendanceMetrics = [
            'tolerancia' => $gracia,
            'total' => $totalRango,
            'asistieron' => null,
            'noShows' => null,
            'asistenciaPorc' => null,
            'registradasPuntualidad' => null,
            'puntualidadPorc' => null,
            'promedioRetraso' => null,
            'camposDisponibles' => false,
        ];

        $hasAttendanceColumns = Schema::hasColumn('citas', 'asistio')
            && Schema::hasColumn('citas', 'inicio_real_at')
            && Schema::hasColumn('citas', 'check_in_at');

        if ($hasAttendanceColumns) {
            $asistieron = (clone $baseQuery)->where('asistio', true)->count();
            $noShows = (clone $baseQuery)->where('asistio', false)->count();

            $registradasPuntualidad = (clone $baseQuery)->whereNotNull('inicio_real_at')->count();
            $puntuales = (clone $baseQuery)
                ->whereNotNull('inicio_real_at')
                ->whereRaw("TIMESTAMPDIFF(MINUTE, CONCAT(fecha, ' ', hora_inicio), inicio_real_at) <= ?", [$gracia])
                ->count();

            $promedioRetraso = (clone $baseQuery)
                ->whereNotNull('inicio_real_at')
                ->selectRaw("AVG(GREATEST(TIMESTAMPDIFF(MINUTE, CONCAT(fecha, ' ', hora_inicio), inicio_real_at), 0)) as retraso")
                ->value('retraso') ?? 0;

            $attendanceMetrics = [
                'tolerancia' => $gracia,
                'total' => $totalRango,
                'asistieron' => $asistieron,
                'noShows' => $noShows,
                'asistenciaPorc' => $totalRango ? round(($asistieron / max($totalRango, 1)) * 100, 1) : 0,
                'registradasPuntualidad' => $registradasPuntualidad,
                'puntualidadPorc' => $registradasPuntualidad ? round(($puntuales / $registradasPuntualidad) * 100, 1) : null,
                'promedioRetraso' => round($promedioRetraso, 1),
                'camposDisponibles' => true,
            ];
        }

        return view('Panel-admin.estadisticas', [
            'chartData' => $chartData,
            'resumenPeriodo' => $resumenPeriodo,
            'attendanceMetrics' => $attendanceMetrics,
            'periodoSeleccionado' => [
                'inicio' => $rangeStart->toDateString(),
                'fin' => $rangeEnd->toDateString(),
            ],
        ]);
    }

    /**
     * Assign a mechanic to a pending appointment and confirm it.
     */
    public function assignCita(Request $request, Cita $cita)
    {
        $validated = $request->validate([
            'mecanico_id' => 'required|exists:mecanicos,id',
        ]);

        if ($cita->estatus === 'cancelada') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No puedes asignar una cita cancelada.');
        }

        $mecanico = Mecanico::withCount(['citas as citas_activas_count' => function ($query) {
                $query->whereNull('deleted_at')
                    ->whereIn('estatus', self::ESTATUS_ACTIVOS);
            }])
            ->findOrFail($validated['mecanico_id']);

        if ($cita->mecanico_id !== $mecanico->id && ($mecanico->citas_activas_count ?? 0) >= 4) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Este mecánico ya alcanzó el máximo de citas activas.');
        }

        $cita->update([
            'mecanico_id' => $validated['mecanico_id'],
            'estatus' => 'confirmada',
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Cita asignada y confirmada correctamente.');
    }

    /**
     * Create mechanic profiles for every user with role 2 if missing.
     */
    private function ensureMechanicProfilesExist(): void
    {
        $existing = Mecanico::pluck('user_id')->all();

        User::where('role_id', 2)
            ->whereNotIn('id', $existing)
            ->each(function (User $user) {
                Mecanico::create([
                    'user_id' => $user->id,
                    'numero_empleado' => 'MECH-' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
                    'especialidad' => null,
                    'activo' => true,
                ]);
            });
    }
}

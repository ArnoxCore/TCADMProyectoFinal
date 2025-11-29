<?php

namespace App\Http\Controllers\Mecanico;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Mecanico;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class MecanicoController extends Controller
{
    public function dashboard(Request $request)
    {
        // Obtener o crear el perfil de mecánico usando el usuario autenticado
        $mecanico = $this->resolveMecanico();

        // Base query builder function para evitar problemas con clones
        $getBaseQuery = function() use ($mecanico) {
            // eager-load mecanico->user to ensure mechanic name is available without extra queries
            $q = Cita::with([
                'cliente.user',
                'vehiculo',
                'servicios',
                'mecanico.user',
                'observaciones' => function ($query) {
                    $query->with('mecanico.user')->latest();
                },
            ]);
            if ($mecanico) {
                $q->where('mecanico_id', $mecanico->id);
            }
            return $q;
        };

        // Política actual: el mecánico ve citas confirmadas y en proceso
        $visibleStatuses = ['confirmada', 'en_proceso'];

        // Citas de hoy (solo pendientes y en_proceso), ordenadas con las de hoy primero
        $citasHoy = $getBaseQuery()
            ->whereIn('estatus', $visibleStatuses)
            ->orderByRaw("DATE(fecha) = CURDATE() DESC")
            ->orderBy('fecha', 'asc')
            ->get();

        // Citas de la semana (próximos 7 días incluyendo hoy) — sólo pendientes y en_proceso
        $citasSemana = $getBaseQuery()
            ->whereBetween('fecha', [today(), today()->addDays(6)])
            ->whereIn('estatus', $visibleStatuses)
            ->orderBy('fecha', 'asc')
            ->get();

        // Citas filtradas por fecha si viene en request
        $citasFiltradas = null;
        if ($request->filled('fecha')) {
            $citasFiltradas = $getBaseQuery()
                ->whereDate('fecha', $request->fecha)
                ->whereIn('estatus', $visibleStatuses)
                ->orderBy('hora_inicio', 'asc')
                ->get();
        }

        // Estadísticas
        $citasHoyCount = $getBaseQuery()->whereDate('fecha', today())->count();
        $enProcesoCount = $getBaseQuery()->where('estatus', 'en_proceso')->count();
        $completadasCount = $getBaseQuery()->where('estatus', 'completada')->count();
        $pendientesCount = $getBaseQuery()->where('estatus', 'pendiente')->count();

        return view('Panel-Mecanico.mecanico', [
            'citasHoy' => $citasHoy,
            'citasSemana' => $citasSemana,
            'citasFiltradas' => $citasFiltradas,
            'mecanico' => $mecanico,
            'citasHoyCount' => $citasHoyCount,
            'enProcesoCount' => $enProcesoCount,
            'completadasCount' => $completadasCount,
            'pendientesCount' => $pendientesCount,
        ]);
    }

    /**
     * Actualizar estatus de una cita
     */
    public function updateEstatus(Request $request, $id)
    {
        // Evitar que un mecánico marque la cita como 'cancelada' desde este endpoint
        $estatus = $request->input('estatus');
        if ($estatus === 'cancelada') {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado: los mecánicos no pueden cancelar citas'
            ], 403);
        }

        $validated = $request->validate([
            'estatus' => 'required|in:en_proceso,completada',
        ]);

        $cita = Cita::findOrFail($id);

        if ($cita->asistio !== true) {
            return response()->json([
                'success' => false,
                'message' => 'Recepción debe registrar la llegada del cliente antes de cambiar el estado.',
            ], 422);
        }

        if ($cita->inicio_real_at === null) {
            return response()->json([
                'success' => false,
                'message' => 'Recepción debe iniciar el servicio antes de que puedas actualizar el estado.',
            ], 422);
        }

        $cita->update(['estatus' => $validated['estatus']]);

        return response()->json(['success' => true, 'message' => 'Estado actualizado', 'estatus' => $cita->estatus_texto]);
    }

    public function storeObservacion(Request $request, Cita $cita)
    {
        $mecanico = $this->resolveMecanico();

        if (! $mecanico) {
            abort(403, 'No se encontró el perfil de mecánico.');
        }

        if ($cita->mecanico_id && $cita->mecanico_id !== $mecanico->id) {
            abort(403, 'Esta cita no está asignada a tu perfil.');
        }

        $validated = $request->validate([
            'observacion' => 'required|string|min:5|max:2000',
        ]);

        $observacion = $cita->observaciones()->create([
            'mecanico_id' => $mecanico->id,
            'tipo' => 'observacion',
            'observacion' => $validated['observacion'],
        ]);

        $payload = [
            'texto' => $observacion->observacion,
            'fecha' => optional($observacion->created_at)
                ? $observacion->created_at->copy()->timezone(Cita::LOCAL_TIMEZONE)->format('d/m/Y H:i')
                : Carbon::now(Cita::LOCAL_TIMEZONE)->format('d/m/Y H:i'),
            'mecanico' => optional($mecanico->user)->name,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Observación registrada correctamente.',
            'observacion' => $payload,
        ]);
    }

    /**
     * Asignar mecánico a una cita
     */
    public function assignMecanico(Request $request, $id)
    {
        $validated = $request->validate([
            'mecanico_id' => 'required|exists:mecanicos,id',
        ]);

        $cita = Cita::findOrFail($id);
        $cita->update(['mecanico_id' => $validated['mecanico_id']]);

        $mecanico = Mecanico::find($validated['mecanico_id']);

        return response()->json([
            'success' => true,
            'message' => 'Mecánico asignado',
            'mecanico_name' => $mecanico->user->name ?? 'Sin nombre',
        ]);
    }

    /**
     * Obtener lista de mecánicos disponibles (JSON)
     */
    public function getMecanicosList()
    {
        $mecanicos = Mecanico::with('user')->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'nombre' => $m->user->name ?? 'Sin nombre',
            ];
        });

        return response()->json($mecanicos);
    }

    private function resolveMecanico(): ?Mecanico
    {
        if (! auth()->check()) {
            return null;
        }

        return Mecanico::firstOrCreate(
            ['user_id' => auth()->id()],
            [
                'numero_empleado' => 'MECH-' . str_pad((string) auth()->id(), 4, '0', STR_PAD_LEFT),
                'especialidad' => null,
                'activo' => true,
            ]
        );
    }
}
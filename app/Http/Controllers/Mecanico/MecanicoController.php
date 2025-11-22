<?php

namespace App\Http\Controllers\Mecanico;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Mecanico;
use Illuminate\Http\Request;

class MecanicoController extends Controller
{
    public function dashboard(Request $request)
    {
        // Obtener el mecánico del usuario autenticado (si existe)
        $mecanico = auth()->check() ? Mecanico::where('user_id', auth()->id())->first() : null;

        // Base query builder function para evitar problemas con clones
        $getBaseQuery = function() use ($mecanico) {
            // eager-load mecanico->user to ensure mechanic name is available without extra queries
            $q = Cita::with(['cliente.user', 'vehiculo', 'servicios', 'mecanico.user']);
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
            'estatus' => 'required|in:pendiente,confirmada,en_proceso,completada',
        ]);

        $cita = Cita::findOrFail($id);
        $cita->update(['estatus' => $validated['estatus']]);

        return response()->json(['success' => true, 'message' => 'Estado actualizado', 'estatus' => $cita->estatus_texto]);
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
}
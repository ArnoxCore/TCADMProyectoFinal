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
        // Fecha que se está viendo (por default hoy)
        $fecha = $request->input('fecha', Carbon::today()->toDateString());

        // Citas de ese día con sus relaciones
        $citasQuery = Cita::with(['cliente.user', 'vehiculo', 'servicio', 'mecanico.user'])
            ->whereDate('fecha', $fecha);

        $citas = (clone $citasQuery)
            ->orderBy('hora_inicio')
            ->get();

        // Métricas para los KPIs
        $stats = [
            'total' => (clone $citasQuery)->count(),
            'pending' => (clone $citasQuery)->where('estatus', 'pendiente')->count(),
            'completed' => (clone $citasQuery)->where('estatus', 'completada')->count(),
            // de inicio igualamos filtradas = total; si luego metemos filtros de servidor se ajusta
            'filtered' => (clone $citasQuery)->count(),
        ];

        // Mecánicos activos para el filtro
        $mecanicos = Mecanico::with('user')
            ->where('activo', true)
            ->get();

        return view('recepcion.dashboard', compact('citas', 'mecanicos', 'fecha', 'stats'));
    }

    // recargar citas vía AJAX al cambiar la fecha
    public function citasPorFecha(Request $request)
    {
        $fecha = $request->input('fecha');

        $citas = Cita::with(['cliente.user', 'vehiculo', 'servicio', 'mecanico.user'])
            ->whereDate('fecha', $fecha)
            ->orderBy('hora_inicio')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $citas,
        ]);
    }
}

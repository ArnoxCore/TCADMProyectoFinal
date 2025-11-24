<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Vehiculo;
use App\Models\Cliente;
use App\Models\Servicio;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $cliente = Cliente::where('user_id', $user->id)
            ->firstOrFail();

        // --------------------------------------------
        // Datos base para el modal
        // --------------------------------------------
        $servicios = Servicio::orderBy('nombre')->get();

        $vehiculos = Vehiculo::where('cliente_id', $cliente->id)
            ->orderBy('marca')
            ->get();

        // --------------------------------------------
        // Stats (tarjetas superiores)
        // --------------------------------------------
        $proximasCitasCount = Cita::where('cliente_id', $cliente->id)
            ->whereIn('estatus', ['pendiente', 'confirmada'])
            ->count();

        $serviciosCompletadosCount = Cita::where('cliente_id', $cliente->id)
            ->where('estatus', 'completada')
            ->count();

        $proximaCita = Cita::where('cliente_id', $cliente->id)
            ->whereIn('estatus', ['pendiente', 'confirmada'])
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->first();

        // --------------------------------------------
        // Tabs (próximas / historial)
        // --------------------------------------------
        $proximas = Cita::where('cliente_id', $cliente->id)
            ->whereIn('estatus', ['pendiente', 'confirmada'])
            ->with(['vehiculo', 'servicios', 'mecanico'])
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        $historial = Cita::where('cliente_id', $cliente->id)
            ->where('estatus', 'completada')
            ->with(['vehiculo', 'servicios', 'mecanico'])
            ->orderByDesc('fecha')
            ->get();

        return view('clientes.dashboard', [
            // Modal
            'servicios' => $servicios,
            'vehiculos' => $vehiculos,

            // Stats
            'proximasCitasCount' => $proximasCitasCount,
            'serviciosCompletadosCount' => $serviciosCompletadosCount,
            'proximaCita' => $proximaCita,

            // Tabs
            'proximas' => $proximas,
            'historial' => $historial,

            'perfilCompleto' => $cliente->isProfileComplete(),
        ]);
    }
}

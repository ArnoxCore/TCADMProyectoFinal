<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\Servicio;
use App\Models\Vehiculo;
use App\Models\Cliente;
use Carbon\Carbon;

class CitaController extends Controller
{
    /**
     * LISTAR CITAS
     */
    public function index()
    {
        $user = auth()->user();
        $cliente = Cliente::where('user_id', $user->id)->firstOrFail();

        $citas = Cita::where('cliente_id', $cliente->id)
            ->with(['vehiculo', 'servicios'])
            ->orderBy('fecha', 'desc')
            ->get();

        return view('clientes.citas.index', compact('citas'));
    }

    /**
     * CREAR CITA
     */
    public function create()
    {
        $user = auth()->user();
        $cliente = Cliente::where('user_id', $user->id)->firstOrFail();

        $vehiculos = Vehiculo::where('cliente_id', $cliente->id)->get();
        $servicios = Servicio::where('activo', 1)->get();

        return view('clientes.citas.crear', compact('vehiculos', 'servicios'));
    }

    /**
     * GUARDAR CITA
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'servicios'   => 'required|array|min:1',
            'servicios.*' => 'exists:servicios,id',
            'fecha'       => 'required|date|after_or_equal:today',
            'hora'        => 'required|date_format:H:i',
        ]);

        $user = auth()->user();
        $cliente = Cliente::where('user_id', $user->id)->firstOrFail();

        $vehiculo = Vehiculo::where('id', $request->vehiculo_id)
            ->where('cliente_id', $cliente->id)
            ->firstOrFail();

        $servicios = Servicio::whereIn('id', $request->servicios)->get();
        $duracionTotal = $servicios->sum('duracion_estimada');

        $horaInicio = Carbon::createFromFormat('H:i', $request->hora);
        $horaFin = $horaInicio->copy()->addMinutes($duracionTotal);

        $cita = Cita::create([
            'cliente_id'  => $cliente->id,
            'vehiculo_id' => $vehiculo->id,
            'mecanico_id' => null,
            'fecha'       => $request->fecha,
            'hora_inicio' => $horaInicio->format('H:i:s'),
            'hora_fin'    => $horaFin->format('H:i:s'),
            'estatus'     => 'pendiente',
            'observaciones_cliente' => $request->observaciones ?? null,
        ]);

        foreach ($servicios as $servicio) {
            $cita->servicios()->attach($servicio->id, [
                'precio_unitario' => $servicio->precio_base,
            ]);
        }

        // Si viene desde AJAX/JSON (fetch), responder en JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Cita creada exitosamente.',
                'cita_id'  => $cita->id,
            ]);
        }

        // Flujo normal (sin AJAX)
        return redirect()
            ->route('cliente.citas.index')
            ->with('success', 'Cita creada exitosamente.');
    }

    /**
     * ACTUALIZAR CITA (FECHA / HORA)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'fecha' => 'required|date|after_or_equal:today',
            'hora'  => 'required|date_format:H:i',
        ]);

        $user = auth()->user();
        $cliente = Cliente::where('user_id', $user->id)->firstOrFail();

        $cita = Cita::where('id', $id)
            ->where('cliente_id', $cliente->id)
            ->with('servicios')
            ->firstOrFail();

        $horaInicio = Carbon::createFromFormat('H:i', $request->hora);
        $horaFin = $horaInicio->copy()->addMinutes(
            $cita->servicios->sum('duracion_estimada')
        );

        $cita->update([
            'fecha'       => $request->fecha,
            'hora_inicio' => $horaInicio->format('H:i:s'),
            'hora_fin'    => $horaFin->format('H:i:s'),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * CANCELAR CITA
     */
    public function cancel(Request $request, $id)
    {
        $user = auth()->user();
        $cliente = Cliente::where('user_id', $user->id)->firstOrFail();

        $cita = Cita::where('id', $id)
            ->where('cliente_id', $cliente->id)
            ->firstOrFail();

        $cita->estatus = 'cancelada';
        $cita->save();

        // Si viene desde fetch/AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'La cita fue cancelada.',
            ]);
        }

        // Flujo normal (submit de form clásico)
        return redirect()
            ->route('cliente.citas.index')
            ->with('success', 'La cita fue cancelada.');
    }
}

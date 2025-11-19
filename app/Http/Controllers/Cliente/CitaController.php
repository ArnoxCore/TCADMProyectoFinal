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
     * LISTAR CITAS DEL CLIENTE
     * GET /mi-cuenta/citas
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
     * MOSTRAR FORMULARIO PARA CREAR UNA CITA
     * GET /mi-cuenta/citas/crear
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
     * GUARDAR UNA CITA
     * POST /mi-cuenta/citas
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'servicios'   => 'required|array|min:1',
            'servicios.*' => 'exists:servicios,id',
            'fecha'       => 'required|date|after_or_equal:today',
            'hora'        => 'required|date_format:H:i',
        ], [
            'vehiculo_id.required' => 'Selecciona un vehículo',
            'servicios.required'   => 'Debes seleccionar al menos un servicio',
            'fecha.after_or_equal' => 'La fecha debe ser hoy o mayor',
        ]);

        $user = auth()->user();
        $cliente = Cliente::where('user_id', $user->id)->firstOrFail();

        // Validar que el vehículo pertenezca al cliente
        $vehiculo = Vehiculo::where('id', $request->vehiculo_id)
            ->where('cliente_id', $cliente->id)
            ->firstOrFail();

        // Obtener duración total = suma de los servicios
        $servicios = Servicio::whereIn('id', $request->servicios)->get();
        $duracionTotal = $servicios->sum('duracion_estimada');

        // Calcular hora_fin
        $horaInicio = Carbon::createFromFormat('H:i', $request->hora);
        $horaFin = $horaInicio->copy()->addMinutes($duracionTotal);

        // Crear cita
        $cita = Cita::create([
            'cliente_id'   => $cliente->id,
            'vehiculo_id'  => $vehiculo->id,
            'mecanico_id'  => null,                   // se asigna después
            'fecha'        => $request->fecha,
            'hora_inicio'  => $horaInicio->format('H:i:s'),
            'hora_fin'     => $horaFin->format('H:i:s'),
            'estatus'      => 'pendiente',
            'observaciones_cliente' => $request->observaciones ?? null,
        ]);

        // Asociar servicios a la cita (pivot)
        foreach ($servicios as $servicio) {
            $cita->servicios()->attach($servicio->id, [
                'precio_unitario' => $servicio->precio_base,
            ]);
        }

        return redirect()
            ->route('cliente.citas.index')
            ->with('success', 'Cita creada exitosamente.');
    }


    /**
     * VER DETALLES DE UNA CITA
     * GET /mi-cuenta/citas/{id}
     */
    public function show($id)
    {
        $user = auth()->user();
        $cliente = Cliente::where('user_id', $user->id)->firstOrFail();

        $cita = Cita::where('id', $id)
            ->where('cliente_id', $cliente->id)
            ->with(['vehiculo', 'servicios'])
            ->firstOrFail();

        return view('clientes.citas.show', compact('cita'));
    }


    /**
     * CANCELAR CITA
     * POST /mi-cuenta/citas/{id}/cancelar
     */
    public function cancel($id)
    {
        $user = auth()->user();
        $cliente = Cliente::where('user_id', $user->id)->firstOrFail();

        $cita = Cita::where('id', $id)
            ->where('cliente_id', $cliente->id)
            ->firstOrFail();

        $cita->estatus = 'cancelada';
        $cita->save();

        return redirect()
            ->route('cliente.citas.index')
            ->with('success', 'La cita fue cancelada.');
    }
}

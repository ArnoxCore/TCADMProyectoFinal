<?php
namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Vehiculo;
use Illuminate\Http\Request;


class CitaController extends Controller
{
    public function index()
    {
        // Obtenemos todos los servicios activos
        $servicios = Servicio::where('activo', 1)->get();

        // Enviamos los servicios a la vista
        return view('panel_cliente', compact('servicios'));
    }

    public function store(Request $request)
    {
        // Validar los datos
        $validated = $request->validate([
            'servicio_id' => 'required|exists:servicios,id',
            'vehiculo' => 'required|string|max:255',
            'fecha' => 'required|date|after:today',
            'hora' => 'required|string',
        ], [
            'servicio_id.required' => 'El servicio es obligatorio',
            'servicio_id.exists' => 'El servicio seleccionado no es válido',
            'vehiculo.required' => 'El vehículo es obligatorio',
            'fecha.required' => 'La fecha es obligatoria',
            'fecha.after' => 'La fecha debe ser mayor a hoy',
            'hora.required' => 'La hora es obligatoria',
        ]);

        try {
            // Obtener o crear el cliente basado en el usuario autenticado
            $usuario = auth()->user();
            $cliente = Cliente::where('user_id', $usuario->id)->first();
            
            if (!$cliente) {
                // Si no existe cliente, crearlo
                $cliente = Cliente::create([
                    'user_id' => $usuario->id,
                    'direccion' => '',
                    'rfc' => '',
                    'fecha_nacimiento' => null,
                ]);
            }

            // Obtener o crear el vehículo
            $vehiculo = Vehiculo::firstOrCreate(
                [
                    'cliente_id' => $cliente->id,
                    'descripcion' => $validated['vehiculo']
                ],
                [
                    'descripcion' => $validated['vehiculo']
                ]
            );

            // Crear la cita
            $cita = new Cita();
            $cita->cliente_id = $cliente->id;
            $cita->vehiculo_id = $vehiculo->id;
            $cita->fecha = $validated['fecha'];
            $cita->hora_inicio = $validated['hora'];
            $cita->estatus = 'pendiente';
            $cita->save();

            // Asociar el servicio a la cita
            $servicio = Servicio::find($validated['servicio_id']);
            $cita->servicios()->attach($servicio->id, [
                'precio_unitario' => $servicio->precio_base
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cita agendada exitosamente',
                'cita' => $cita
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agendar la cita: ' . $e->getMessage()
            ], 500);
        }
    }
}

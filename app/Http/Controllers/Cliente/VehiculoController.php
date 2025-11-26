<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Vehiculo;
use App\Models\Cliente;
use App\Models\VehicleMake;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class VehiculoController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $cliente = Cliente::where('user_id', $user->id)->first();

        $vehiculos = Vehiculo::where('cliente_id', $cliente->id)->get();

        return view('clientes.vehiculos.index', compact('vehiculos'));
    }

    public function create()
    {
        $marcas = VehicleMake::orderBy('nombre')->get();

        return view('clientes.vehiculos.crear', compact('marcas'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'marca'        => ['required', 'string', 'max:100', Rule::exists('vehicle_makes', 'nombre')],
            'modelo'       => ['required', 'string', 'max:150'],
            'ano'          => ['required', 'integer', 'min:1900', 'max:2100'],
            'placa'        => 'required|string|max:20|unique:vehiculos,placa|regex:/^[A-Z]{3}-\d{3}-[A-Z0-9]$/',
            'vin'          => ['required', 'string', 'size:17', 'regex:/^[A-HJ-NPR-Z0-9]{17}$/', Rule::unique('vehiculos', 'vin')],
            'color'        => 'required|string|max:50',
            'kilometraje'  => 'required|integer|min:0',
            'vin_verificado' => 'nullable|boolean',
            'vin_detected_marca' => 'nullable|string|max:100',
            'vin_detected_modelo' => 'nullable|string|max:150',
            'vin_detected_ano' => 'nullable|integer|min:1900|max:2100',
        ], [
            'placa.regex' => 'El formato de la placa es inválido. Ejemplo: ABC-123-A',
            'vin.unique'  => 'Este VIN ya está registrado en la plataforma.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $marca = VehicleMake::where('nombre', $request->input('marca'))->first();
            if (! $marca) {
                return;
            }

            $modelo = $marca->modelos()
                ->where('nombre', $request->input('modelo'))
                ->first();

            if (! $modelo) {
                $validator->errors()->add('modelo', 'El modelo seleccionado no pertenece a la marca.');
                return;
            }

            $existeAno = $modelo->years()
                ->where('year', (int) $request->input('ano'))
                ->exists();

            if (! $existeAno) {
                $validator->errors()->add('ano', 'El año seleccionado no está disponible para el modelo elegido.');
            }
        });

        $validator->validate();

        $user = auth()->user();
        $cliente = Cliente::where('user_id', $user->id)->firstOrFail();

        Vehiculo::create([
            'cliente_id' => $cliente->id,
            'marca'      => $request->marca,
            'modelo'     => $request->modelo,
            'ano'       => $request->ano,
            'placa'      => $request->placa,
            'vin'        => $request->vin ?? null,
            'color'      => $request->color ?? null,
            'kilometraje'=> $request->kilometraje ?? null,
            'vin_verificado' => filter_var($request->input('vin_verificado'), FILTER_VALIDATE_BOOLEAN),
            'vin_detected_marca' => $request->vin_detected_marca,
            'vin_detected_modelo' => $request->vin_detected_modelo,
            'vin_detected_ano' => $request->vin_detected_ano,
        ]);

        return redirect()->route('cliente.vehiculos.index')
            ->with('success', 'Vehículo agregado correctamente.');
    }

    public function edit($id)
    {
        $user = auth()->user();
        $cliente = Cliente::where('user_id', $user->id)->first();

        $vehiculo = Vehiculo::where('id', $id)
            ->where('cliente_id', $cliente->id)
            ->firstOrFail();

        $marcas = VehicleMake::orderBy('nombre')->get();

        return view('clientes.vehiculos.editar', compact('vehiculo', 'marcas'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'marca'  => ['required', 'string', 'max:100', Rule::exists('vehicle_makes', 'nombre')],
            'modelo' => ['required', 'string', 'max:150'],
            'ano'   => ['required', 'integer', 'min:1900', 'max:2100'],
            'placa'  => 'required|string|max:20|unique:vehiculos,placa,' . $id . '|regex:/^[A-Z]{3}-\d{3}-[A-Z0-9]$/',
            'vin'    => ['required', 'string', 'size:17', 'regex:/^[A-HJ-NPR-Z0-9]{17}$/', Rule::unique('vehiculos', 'vin')->ignore($id)],
            'color'  => 'nullable|string|max:50',
            'kilometraje' => 'nullable|integer|min:0',
            'vin_verificado' => 'nullable|boolean',
            'vin_detected_marca' => 'nullable|string|max:100',
            'vin_detected_modelo' => 'nullable|string|max:150',
            'vin_detected_ano' => 'nullable|integer|min:1900|max:2100',
        ], [
            'vin.unique' => 'Este VIN ya está registrado en la plataforma.',
            'placa.regex' => 'El formato de la placa es inválido. Ejemplo: ABC-123-A',
        ]);

        $validator->after(function ($validator) use ($request) {
            $marca = VehicleMake::where('nombre', $request->input('marca'))->first();
            if (! $marca) {
                return;
            }

            $modelo = $marca->modelos()
                ->where('nombre', $request->input('modelo'))
                ->first();

            if (! $modelo) {
                $validator->errors()->add('modelo', 'El modelo seleccionado no pertenece a la marca.');
                return;
            }

            $existeAno = $modelo->years()
                ->where('year', (int) $request->input('ano'))
                ->exists();

            if (! $existeAno) {
                $validator->errors()->add('ano', 'El año seleccionado no está disponible para el modelo elegido.');
            }
        });

        $validator->validate();

        $user = auth()->user();
        $cliente = Cliente::where('user_id', $user->id)->first();

        $vehiculo = Vehiculo::where('id', $id)
            ->where('cliente_id', $cliente->id)
            ->firstOrFail();

        $payload = $request->only([
            'marca',
            'modelo',
            'ano',
            'placa',
            'vin',
            'color',
            'kilometraje',
            'vin_detected_marca',
            'vin_detected_modelo',
            'vin_detected_ano',
        ]);

        $payload['vin_verificado'] = filter_var($request->input('vin_verificado'), FILTER_VALIDATE_BOOLEAN);

        $vehiculo->update($payload);

        return redirect()->route('cliente.vehiculos.index')
            ->with('success', 'Vehículo actualizado correctamente.');
    }
}

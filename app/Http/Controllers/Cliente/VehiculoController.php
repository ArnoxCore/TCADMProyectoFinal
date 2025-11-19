<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Vehiculo;
use App\Models\Cliente;
use Illuminate\Http\Request;

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
        return view('clientes.vehiculos.crear');
    }

    public function store(Request $request)
    {
        $request->validate([
            'marca'        => 'required|string|max:100',
            'modelo'       => 'required|string|max:100',
            'ano'          => 'required|integer|min:1900|max:2100',
            'placa'        => 'required|string|max:20|unique:vehiculos,placa|regex:/^[A-Z]{3}-\d{3}-[A-Z0-9]{1,2}$/',
            'vin'          => 'required|string|size:17|regex:/^[A-HJ-NPR-Z0-9]{17}$/',
            'color'        => 'required|string|max:50',
            'kilometraje'  => 'required|integer|min:0',
        ], [
            'placa.regex' => 'El formato de la placa es inválido. Ejemplo: ABC-123-A',
        ]);

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

        return view('clientes.vehiculos.editar', compact('vehiculo'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'marca'  => 'required|string|max:100',
            'modelo' => 'required|string|max:100',
            'ano'   => 'required|integer|min:1900|max:2100',
            'placa'  => 'required|string|max:20|unique:vehiculos,placa,' . $id,
        ]);

        $user = auth()->user();
        $cliente = Cliente::where('user_id', $user->id)->first();

        $vehiculo = Vehiculo::where('id', $id)
            ->where('cliente_id', $cliente->id)
            ->firstOrFail();

        $vehiculo->update($request->all());

        return redirect()->route('cliente.vehiculos.index')
            ->with('success', 'Vehículo actualizado correctamente.');
    }
}

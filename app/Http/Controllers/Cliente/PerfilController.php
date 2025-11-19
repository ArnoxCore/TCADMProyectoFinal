<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cliente;
use Exception;

class PerfilController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $cliente = Cliente::where('user_id', $user->id)->first();

        return view('clientes.perfil.editar', compact('user', 'cliente'));
    }

    public function update(Request $request)
    {
        // Validación
        $request->validate([
            'name'   => 'required|string|max:255',
            'phone'  => 'required|string|max:20',
            'direccion' => 'nullable|string|max:255',

            'rfc' => [
                'nullable',
                'string',
                'max:13',
                'regex:/^([A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3})$/'
            ],

            'fecha_nacimiento' => 'nullable|date',

        ], [
            'rfc.regex' => 'El RFC no cumple con el formato oficial del SAT.',
            'rfc.max'   => 'El RFC no puede tener más de 13 caracteres.',
            'name.required' => 'El nombre es obligatorio.',
            'phone.required' => 'El número de teléfono es obligatorio.',
        ]);

        try {

            $user = auth()->user();
            $cliente = Cliente::where('user_id', $user->id)->firstOrFail();

            // Actualizar tabla users
            $user->update([
                'name'  => $request->name,
                'phone' => $request->phone,
            ]);

            // Actualizar tabla clientes
            $cliente->update([
                'direccion' => $request->direccion,
                'rfc'       => $request->rfc,
                'fecha_nacimiento' => $request->fecha_nacimiento ?? null,
            ]);

            return redirect()
                ->route('cliente.perfil')
                ->with('success', 'Perfil actualizado correctamente.');

        } catch (Exception $e) {

            return redirect()
                ->route('cliente.perfil')
                ->with('error', 'Ocurrió un error al guardar los datos. Intenta más tarde.');
        }
    }
}

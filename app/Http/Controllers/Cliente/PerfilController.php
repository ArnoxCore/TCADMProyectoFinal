<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cliente;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
        $user = auth()->user();

        // Validación
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => [
                'required',
                'string',
                'max:255',
                'email:dns',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
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
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Ese correo ya está en uso.',
            'rfc.regex' => 'El RFC no cumple con el formato oficial del SAT.',
            'rfc.max'   => 'El RFC no puede tener más de 13 caracteres.',
            'name.required' => 'El nombre es obligatorio.',
            'phone.required' => 'El número de teléfono es obligatorio.',
        ]);

        try {
            DB::transaction(function () use ($user, $validated) {
                $cliente = Cliente::where('user_id', $user->id)->firstOrFail();

                // Actualizar tabla users
                $user->update([
                    'name'  => $validated['name'],
                    'email' => trim($validated['email']),
                    'phone' => $validated['phone'],
                ]);

                // Actualizar tabla clientes
                $cliente->update([
                    'direccion' => $validated['direccion'] ?? null,
                    'rfc'       => $validated['rfc'] ?? null,
                    'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                ]);
            });

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

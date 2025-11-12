<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Mostrar vista de registro.
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Procesar registro.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'regex:/^(\+?\d{10,15})$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Crear usuario con rol de Cliente
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role_id' => 1, // Cliente por defecto
        ]);

        // Crear registro en la tabla clientes (1:1 con users)
        Cliente::create([
            'user_id' => $user->id,
        ]);

        // No iniciar sesión automáticamente
        return redirect()->route('login')->with('success', 'Cuenta creada con éxito. Ahora inicia sesión.');
    }
}

<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Throwable;

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
        $user = $request->user();

        if ($request->filled('rfc')) {
            $request->merge(['rfc' => strtoupper($request->input('rfc'))]);
        }

        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone'  => ['required', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'rfc' => ['nullable', 'string', 'max:13', 'regex:/^([A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3})$/'],
            'fecha_nacimiento' => ['nullable', 'date'],
        ], [
            'rfc.regex' => 'El RFC no cumple con el formato oficial del SAT.',
            'rfc.max'   => 'El RFC no puede tener más de 13 caracteres.',
            'name.required' => 'El nombre es obligatorio.',
            'phone.required' => 'El número de teléfono es obligatorio.',
        ]);

        try {
            $cliente = Cliente::where('user_id', $user->id)->firstOrFail();

            $shouldLogout = false;

            if ($user->email !== $validated['email']) {
                $shouldLogout = true;
                $user->email_verified_at = null;
            }

            $user->fill([
                'name'  => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ]);

            if ($shouldLogout) {
                $user->force_logout_at = now();
            }

            $user->save();

            $cliente->update([
                'direccion' => $validated['direccion'] ?? null,
                'rfc' => $validated['rfc'] ?? null,
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
            ]);

            if ($shouldLogout) {
                return $this->logoutAndRedirect($request, __('Tu información fue actualizada. Inicia sesión nuevamente.'));
            }

            return redirect()->route('cliente.perfil')->with('success', 'Perfil actualizado correctamente.');
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Ocurrió un error al guardar los datos. Intenta más tarde.');
        }
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();
        $user->password = $validated['password'];
        $user->force_logout_at = now();
        $user->save();

        return $this->logoutAndRedirect($request, __('Tu contraseña fue actualizada. Inicia sesión con los nuevos datos.'));
    }

    private function logoutAndRedirect(Request $request, string $message)
    {
        $user = $request->user();

        if ($user && ! $user->force_logout_at) {
            $user->force_logout_at = now();
            $user->save();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', $message);
    }
}

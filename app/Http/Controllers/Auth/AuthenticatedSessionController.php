<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        switch (Auth::user()->role_id) {

            case 1: // Cliente
                return redirect()->intended('/mi-cuenta');

            case 2: // Mecánico
                return redirect()->intended('/mecanico');

            case 3: // Recepcionista
                return redirect()->intended('/recepcion');

            case 4: // Admin
                return redirect()->intended('/admin');

            default:
                return redirect('/');
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Redirigir explícitamente a la ruta de login tras cerrar sesión
        return redirect()->route('login');
    }
}

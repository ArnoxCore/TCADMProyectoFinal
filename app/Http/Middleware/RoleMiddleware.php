<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $roleId)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // Si el rol no coincide, redirigir al panel correcto
        if ($user->role_id != $roleId) {

            switch ($user->role_id) {

                case 1: // Cliente
                    return redirect('/mi-cuenta');

                case 2: // Mecánico
                    return redirect('/mecanico');

                case 3: // Recepcionista
                    return redirect('/recepcion');

                case 4: // Admin
                    return redirect('/admin');

                default:
                    // Cualquier rol desconocido → login
                    return redirect('/login');
            }
        }

        // Rol correcto → permitir acceso
        return $next($request);
    }
}

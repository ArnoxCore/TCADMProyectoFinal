<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnforceForceLogout
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user) {
            $forceTimestamp = optional($user->force_logout_at)?->timestamp ?? 0;
            $sessionTimestamp = $request->session()->get('force_logout_at');

            if ($request->session()->has('force_logout_at') && $forceTimestamp !== $sessionTimestamp) {
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('status', __('Tus credenciales fueron actualizadas. Inicia sesión nuevamente.'));
            }

            if ($sessionTimestamp !== $forceTimestamp) {
                $request->session()->put('force_logout_at', $forceTimestamp);
            }
        }

        return $next($request);
    }
}

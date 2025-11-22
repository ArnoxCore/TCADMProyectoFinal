<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display the administrator dashboard panel.
     */
    public function dashboard(Request $request)
    {
        // Render the existing Blade file located outside de default views folder
        return view()->file(resource_path('Panel-admin/Index.blade.php'));
    }

    /**
     * Show services management panel.
     */
    public function servicios(Request $request)
    {
        return view()->file(resource_path('Panel-admin/servicios.blade.php'));
    }

    /**
     * Show reports panel.
     */
    public function reportes(Request $request)
    {
        return view()->file(resource_path('Panel-admin/reportes.blade.php'));
    }

    /**
     * Show statistics dashboard panel.
     */
    public function estadisticas(Request $request)
    {
        return view()->file(resource_path('Panel-admin/estadisticas.blade.php'));
    }
}

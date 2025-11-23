<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Servicio;

class AdminController extends Controller
{
    /**
     * Display the administrator dashboard panel.
     */
    public function dashboard(Request $request)
    {
        return view('Panel-admin.Index');
    }

    /**
     * Show services management panel.
     */
    public function servicios(Request $request)
    {
        $servicios = Servicio::orderBy('nombre')->paginate(10);

        return view('Panel-admin.servicios', compact('servicios'));
    }

    /**
     * Store a new service created from the admin panel modal.
     */
    public function storeServicio(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'duracion_estimada' => 'required|integer|min:1',
            'precio_base' => 'required|numeric|min:0',
        ]);

        Servicio::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'duracion_estimada' => $validated['duracion_estimada'],
            'precio_base' => $validated['precio_base'],
            'activo' => true,
        ]);

        return redirect()->route('admin.servicios')
            ->with('success', 'Servicio creado correctamente.');
    }

    /**
     * Update an existing service.
     */
    public function updateServicio(Request $request, Servicio $servicio)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'duracion_estimada' => 'required|integer|min:1',
            'precio_base' => 'required|numeric|min:0',
        ]);

        $servicio->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'duracion_estimada' => $validated['duracion_estimada'],
            'precio_base' => $validated['precio_base'],
        ]);

        return redirect()->route('admin.servicios')
            ->with('success', 'Servicio actualizado correctamente.');
    }

    /**
     * Delete a service from catalog.
     */
    public function destroyServicio(Servicio $servicio)
    {
        $nombre = $servicio->nombre;
        $servicio->delete();

        return redirect()->route('admin.servicios')
            ->with('success', "Servicio '{$nombre}' eliminado correctamente.");
    }

    /**
     * Show reports panel.
     */
    public function reportes(Request $request)
    {
        return view('Panel-admin.reportes');
    }

    /**
     * Show statistics dashboard panel.
     */
    public function estadisticas(Request $request)
    {
        return view('Panel-admin.estadisticas');
    }
}

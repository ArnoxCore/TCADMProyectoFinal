<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VehicleMake;
use Illuminate\Http\JsonResponse;

class VehicleCatalogController extends Controller
{
    public function modelos(VehicleMake $make): JsonResponse
    {
        $modelos = $make->modelos()
            ->with(['years' => fn ($query) => $query->orderBy('year')])
            ->orderBy('nombre')
            ->get()
            ->map(function ($modelo) {
                return [
                    'nombre' => $modelo->nombre,
                    'years' => $modelo->years->pluck('year')->values(),
                ];
            });

        return response()->json([
            'make' => $make->nombre,
            'modelos' => $modelos,
        ]);
    }
}

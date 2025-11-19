<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CarsXEController extends Controller
{
    public function getImage(Request $request)
    {
        $make  = $request->make;
        $model = $request->model;
        $year  = $request->year;

        if (!$make || !$model || !$year) {
            return response()->json([
                'success' => false,
                'message' => 'Missing parameters'
            ], 400);
        }

        $apiKey = env('CARSXE_API_KEY');

        $response = Http::get("https://api.carsxe.com/images", [
            'key'   => $apiKey,
            'make'  => $make,
            'model' => $model,
            'year'  => $year,
        ]);

        if (!$response->ok()) {
            return response()->json([
                'success' => false,
                'message' => 'CarsXE request failed'
            ], 500);
        }

        $data = $response->json();

        if (!isset($data["images"]) || count($data["images"]) === 0) {
            return response()->json([
                'success' => true,
                'image'   => null,
            ]);
        }

        // 👇 AQUI ESTABA EL PROBLEMA
        return response()->json([
            'success' => true,
            'image'   => $data["images"][0]["link"] ?? null
        ]);
    }
}

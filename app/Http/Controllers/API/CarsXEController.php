<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CarsXEController extends Controller
{
    public function getImage(Request $request)
    {
        $make = $request->query('make');
        $model = $request->query('model');
        $year = $request->query('year');

        if (! $make || ! $model || ! $year) {
            return response()->json([
                'success' => false,
                'image' => null,
                'message' => 'Missing parameters',
            ], 200); // 200 para que el front use fallback
        }

        // 👇 Usar el config, no env() directo
        $apiKey = config('services.carsxe.key');

        if (! $apiKey) {
            Log::warning('CarsXE: API key missing (config services.carsxe.key es null)');

            return response()->json([
                'success' => true,
                'image' => null,
                'message' => 'API key missing',
            ], 200);
        }

        try {
            $response = Http::timeout(7)->get('https://api.carsxe.com/images', [
                'key' => $apiKey,
                'make' => $make,
                'model' => $model,
                'year' => $year,
            ]);
        } catch (\Throwable $e) {
            Log::error('CarsXE exception', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => true,
                'image' => null,
                'message' => 'Exception calling CarsXE',
            ], 200);
        }

        if (! $response->successful()) {
            Log::error('CarsXE bad status', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'success' => true,
                'image' => null,
                'message' => 'CarsXE returned bad status',
            ], 200);
        }

        $data = $response->json();

        Log::info('CarsXE raw response', [
            'status' => $response->status(),
            'data' => $data,
        ]);

        $image = null;

        if (isset($data['images']) && is_array($data['images']) && count($data['images']) > 0) {
            $first = $data['images'][0];

            if (is_array($first)) {
                $image = $first['link'] ?? $first['url'] ?? null;
            } elseif (is_string($first)) {
                $image = $first;
            }
        }

        return response()->json([
            'success' => true,
            'image' => $image,
        ], 200);
    }
}

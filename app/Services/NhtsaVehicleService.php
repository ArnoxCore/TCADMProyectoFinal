<?php

namespace App\Services;

use App\Models\VehicleMake;
use App\Models\VehicleModel;
use App\Models\VehicleModelYear;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class NhtsaVehicleService
{
    private const BASE_URL = 'https://vpic.nhtsa.dot.gov/api/vehicles';

    /**
     * Descarga y actualiza catálogo de marcas/modelos/años.
     *
     * @param array<int> $years Lista de años a sincronizar. Si se entrega vacío, se usará el año actual.
     * @param int|null $limit número máximo de marcas (para pruebas).
     * @param callable|null $progress Callback opcional para reportar avances por marca.
     */
    public function sync(array $years = [], ?int $limit = null, ?callable $progress = null): array
    {
        return $this->syncInternal($years, $limit, null, $progress);
    }

    /**
     * Sincroniza una marca específica por nombre.
     */
    public function syncMake(string $makeName, array $years = [], ?callable $progress = null): array
    {
        $years = $this->normalizarYears($years);
        $make = $this->buscarMarcaPorNombre($makeName);

        if (! $make) {
            throw new \InvalidArgumentException("La marca {$makeName} no fue encontrada en la NHTSA.");
        }

        $storedMake = VehicleMake::updateOrCreate(
            ['nhtsa_id' => $make['Make_ID']],
            ['nombre' => Str::upper($make['Make_Name'])]
        );

        $modelsSync = $this->syncModelsForMake($storedMake, $years);
        if ($progress) {
            $progress($storedMake->nombre, $modelsSync['models'], $modelsSync['years']);
        }

        return [
            'importedMakes' => 1,
            'importedModels' => $modelsSync['models'],
            'importedYears' => $modelsSync['years'],
        ];
    }

    private function syncInternal(array $years = [], ?int $limit = null, ?array $onlyMake = null, ?callable $progress = null): array
    {
        $years = $this->normalizarYears($years);
        $makesResponse = Http::retry(3, 500)->get(self::BASE_URL.'/GetAllMakes', [
            'format' => 'json',
        ]);

        $results = $makesResponse->json('Results', []);
        $collection = collect($results);

        if ($onlyMake) {
            $collection = $collection->filter(fn ($data) => Str::upper($data['Make_Name'] ?? '') === $onlyMake['name']);
        }

        if ($limit) {
            $collection = $collection->take($limit);
        }

        $importedMakes = 0;
        $importedModels = 0;
        $importedYears = 0;

        foreach ($collection as $item) {
            $makeId = Arr::get($item, 'Make_ID');
            $makeName = Str::upper(Arr::get($item, 'Make_Name', ''));

            if (!$makeId || !$makeName) {
                continue;
            }

            $make = VehicleMake::updateOrCreate(
                ['nhtsa_id' => $makeId],
                ['nombre' => $makeName]
            );
            $importedMakes++;

            $modelsSync = $this->syncModelsForMake($make, $years);
            $importedModels += $modelsSync['models'];
            $importedYears += $modelsSync['years'];

            if ($progress) {
                $progress($makeName, $modelsSync['models'], $modelsSync['years']);
            }
        }

        return compact('importedMakes', 'importedModels', 'importedYears');
    }

    private function buscarMarcaPorNombre(string $makeName): ?array
    {
        $response = Http::retry(3, 500)->get(self::BASE_URL.'/GetAllMakes', [
            'format' => 'json',
        ]);

        $collection = collect($response->json('Results', []));
        $upper = Str::upper($makeName);

        $match = $collection->first(fn ($item) => Str::upper($item['Make_Name'] ?? '') === $upper);

        return $match ? ['Make_ID' => $match['Make_ID'] ?? null, 'Make_Name' => $match['Make_Name'] ?? $upper] : null;
    }

    /**
     * @param VehicleMake $make
     * @param array<int> $years
     * @return array{models:int, years:int}
     */
    private function syncModelsForMake(VehicleMake $make, array $years): array
    {
        $modelsCount = 0;
        $yearsCount = 0;

        foreach ($years as $year) {
            try {
                $response = Http::retry(3, 500)->get(self::BASE_URL.'/GetModelsForMakeIdYear', [
                    'format' => 'json',
                    'makeId' => $make->nhtsa_id,
                    'modelYear' => $year,
                ]);
            } catch (Throwable $exception) {
                report($exception);
                continue;
            }

            $models = collect($response->json('Results', []));
            if ($models->isEmpty()) {
                continue;
            }

            foreach ($models as $item) {
                $modelId = Arr::get($item, 'Model_ID');
                $modelName = Str::upper(Arr::get($item, 'Model_Name', ''));

                if (!$modelName) {
                    continue;
                }

                $model = VehicleModel::updateOrCreate(
                    [
                        'vehicle_make_id' => $make->id,
                        'nombre' => $modelName,
                    ],
                    [
                        'nhtsa_id' => $modelId,
                    ]
                );

                $modelsCount++;

                VehicleModelYear::updateOrCreate(
                    [
                        'vehicle_model_id' => $model->id,
                        'year' => $year,
                    ]
                );
                $yearsCount++;
            }
        }

        return ['models' => $modelsCount, 'years' => $yearsCount];
    }

    /**
     * @param array<int|string|null> $years
     * @return array<int>
     */
    private function normalizarYears(array $years): array
    {
        if (empty($years)) {
            return [now()->year];
        }

        return collect($years)
            ->filter()
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}

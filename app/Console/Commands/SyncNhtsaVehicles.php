<?php

namespace App\Console\Commands;

use App\Services\NhtsaVehicleService;
use Illuminate\Console\Command;

class SyncNhtsaVehicles extends Command
{
    protected $signature = 'vehiculos:sync-nhtsa'
        .' {--year=* : Años individuales a sincronizar (puedes repetir la opción)}'
        .' {--year-range=* : Rangos de años, ejemplo 2018-2024}'
        .' {--limit= : Limitar número de marcas (para pruebas)}'
        .' {--make= : Nombre exacto de la marca a sincronizar}';

    protected $description = 'Sincroniza marcas, modelos y años desde la API de la NHTSA';

    public function handle(NhtsaVehicleService $service): int
    {
        $years = $this->resolveYears();
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $make = $this->option('make');

        $this->info('Iniciando sincronización con NHTSA...');

        if ($make) {
            $resultados = $service->syncMake($make, $years, function (string $makeName, int $models, int $yearsCount) {
                $this->line(sprintf('   • %s: %d modelos, %d registros año-modelo', $makeName, $models, $yearsCount));
            });
        } else {
            $resultados = $service->sync($years, $limit, function (string $makeName, int $models, int $yearsCount) {
                $this->line(sprintf('   • %s: %d modelos, %d registros año-modelo', $makeName, $models, $yearsCount));
            });
        }

        $this->info(sprintf(
            'Importadas %d marcas, %d modelos y %d combinaciones modelo-año.',
            $resultados['importedMakes'],
            $resultados['importedModels'],
            $resultados['importedYears']
        ));

        return self::SUCCESS;
    }

    /**
     * Obtiene el listado final de años a sincronizar, combinando opciones simples y rangos.
     */
    private function resolveYears(): array
    {
        $years = collect((array) $this->option('year'))
            ->filter(fn ($year) => $year !== null && $year !== '')
            ->map(fn ($year) => (int) $year);

        foreach ((array) $this->option('year-range') as $range) {
            if (! $range) {
                continue;
            }

            if (! preg_match('/^(\d{4})-(\d{4})$/', trim($range), $matches)) {
                $this->warn("Formato de rango inválido: {$range}. Usa el formato 2018-2024.");
                continue;
            }

            $start = (int) $matches[1];
            $end = (int) $matches[2];

            if ($start > $end) {
                [$start, $end] = [$end, $start];
            }

            foreach (range($start, $end) as $year) {
                $years->push($year);
            }
        }

        return $years->unique()->sort()->values()->all();
    }
}

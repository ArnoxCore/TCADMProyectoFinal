<?php

namespace App\Console\Commands;

use App\Services\NhtsaVehicleService;
use Illuminate\Console\Command;

class SyncNhtsaVehicles extends Command
{
    protected $signature = 'vehiculos:sync-nhtsa {--year=* : Años a sincronizar} {--limit= : Limitar número de marcas (para pruebas)} {--make= : Nombre exacto de la marca a sincronizar}';

    protected $description = 'Sincroniza marcas, modelos y años desde la API de la NHTSA';

    public function handle(NhtsaVehicleService $service): int
    {
        $years = (array) $this->option('year');
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
}

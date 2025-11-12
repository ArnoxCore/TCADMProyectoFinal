<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Servicio;

class ServiciosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $servicios = [
            [
                'nombre' => 'Cambio de Aceite',
                'descripcion' => 'Cambio de aceite y filtro',
                'duracion_estimada' => 30,
                'precio_base' => 500,
                'activo' => 1,
            ],
            [
                'nombre' => 'Reparación de Frenos',
                'descripcion' => 'Revisión y reparación del sistema de frenos',
                'duracion_estimada' => 60,
                'precio_base' => 300,
                'activo' => 1,
            ],
            [
                'nombre' => 'Alineación de Ruedas',
                'descripcion' => 'Alineación y balanceo de ruedas',
                'duracion_estimada' => 45,
                'precio_base' => 80,
                'activo' => 1,
            ],
            [
                'nombre' => 'Revisión Completa',
                'descripcion' => 'Revisión general del vehículo',
                'duracion_estimada' => 90,
                'precio_base' => 1000,
                'activo' => 1,
            ],
            [
                'nombre' => 'Cambio de Neumáticos',
                'descripcion' => 'Cambio de uno o más neumáticos',
                'duracion_estimada' => 40,
                'precio_base' => 200,
                'activo' => 1,
            ],
        ];

        foreach ($servicios as $servicio) {
            Servicio::create($servicio);
        }
    }
}

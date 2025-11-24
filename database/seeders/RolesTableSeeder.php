<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder {
    public function run(): void {
        $now = now();
        DB::table('roles')->insert([
            ['name' => 'Cliente',       'description' => 'Puede agendar/modificar/cancelar citas y ver historial', 'created_at'=>$now,'updated_at'=>$now],
            ['name' => 'Mecánico',      'description' => 'Ve citas asignadas, actualiza estatus y agrega observaciones', 'created_at'=>$now,'updated_at'=>$now],
            ['name' => 'Recepcionista', 'description' => 'Gestiona calendario y asignación de mecánicos', 'created_at'=>$now,'updated_at'=>$now],
            ['name' => 'Administrador', 'description' => 'Gestión total: usuarios, servicios, reportes, config', 'created_at'=>$now,'updated_at'=>$now],
        ]);
    }
}
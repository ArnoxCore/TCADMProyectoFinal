<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Cliente;

class ClienteTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear un usuario de prueba
        $user = User::firstOrCreate(
            ['email' => 'cliente@test.com'],
            [
                'name' => 'Cliente Test',
                'password' => bcrypt('password123'),
                'phone' => '5551234567',
            ]
        );

        // Crear cliente asociado al usuario
        Cliente::firstOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'direccion' => 'Calle Principal 123',
                'rfc' => 'CLTE850101ABC',
                'fecha_nacimiento' => '1985-01-01',
            ]
        );
    }
}

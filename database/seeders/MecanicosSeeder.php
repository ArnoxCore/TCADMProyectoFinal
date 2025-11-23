<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Mecanico;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MecanicosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 👇 Ajusta este rol_id si tus mecánicos usan otro
        $ROL_MECANICO = 2;

        $mecanicosData = [
            [
                'name'           => 'Juan Mecánico',
                'email'          => 'mecanico1@example.com',
                'numeroEmpleado' => 'MEC-001',
                'especialidad'   => 'Frenos y suspensión',
            ],
            [
                'name'           => 'Ana Taller',
                'email'          => 'mecanico2@example.com',
                'numeroEmpleado' => 'MEC-002',
                'especialidad'   => 'Motor y transmisión',
            ],
            [
                'name'           => 'Carlos Torque',
                'email'          => 'mecanico3@example.com',
                'numeroEmpleado' => 'MEC-003',
                'especialidad'   => 'Diagnóstico eléctrico',
            ],
        ];

        foreach ($mecanicosData as $data) {
            // 1) Crear / recuperar usuario
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => Hash::make('quinones'),
                    'role_id'  => $ROL_MECANICO,
                ]
            );

            // 2) Crear / recuperar mecánico ligado a ese user
            Mecanico::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'numero_empleado' => $data['numeroEmpleado'],
                    'especialidad'    => $data['especialidad'],
                    'activo'          => true,
                ]
            );
        }
    }
}

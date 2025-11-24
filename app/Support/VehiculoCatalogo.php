<?php

namespace App\Support;

class VehiculoCatalogo
{
    public static function obtener(): array
    {
        $catalogo = config('vehiculos.catalogo');

        if (!empty($catalogo)) {
            return $catalogo;
        }

        $ruta = base_path('config/vehiculos.php');
        if (file_exists($ruta)) {
            $data = require $ruta;
            if (is_array($data) && isset($data['catalogo'])) {
                return $data['catalogo'];
            }
        }

        return [];
    }
}

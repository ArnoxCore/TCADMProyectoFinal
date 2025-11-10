<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Servicio extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable = ['nombre','descripcion','duracion_estimada','precio_base','activo'];

    public function citas(): BelongsToMany {
        return $this->belongsToMany(Cita::class, 'citas_servicios')
                    ->withPivot(['precio_unitario'])
                    ->withTimestamps();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cita extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cliente_id','vehiculo_id','mecanico_id','fecha','hora_inicio','hora_fin',
        'estatus','observaciones_cliente','precio_final'
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora_inicio' => 'datetime:H:i:s',
        'hora_fin' => 'datetime:H:i:s',
    ];

    public function cliente(): BelongsTo { return $this->belongsTo(Cliente::class); }
    public function vehiculo(): BelongsTo { return $this->belongsTo(Vehiculo::class); }
    public function mecanico(): BelongsTo { return $this->belongsTo(Mecanico::class); }

    public function servicios(): BelongsToMany {
        return $this->belongsToMany(Servicio::class, 'citas_servicios')
                    ->withPivot(['precio_unitario'])
                    ->withTimestamps();
    }

    public function observaciones(): HasMany {
        return $this->hasMany(ObservacionServicio::class);
    }
}
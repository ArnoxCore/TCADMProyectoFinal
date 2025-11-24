<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservacionServicio extends Model {
    use HasFactory;
    protected $table = 'observaciones_servicio';
    protected $fillable = [
        'cita_id','mecanico_id','tipo','observacion',
        'requiere_reparacion','aprobada','aprobada_at',
        'costo_pieza','costo_mano_obra'
    ];

    protected $casts = [
        'aprobada' => 'boolean',
        'aprobada_at' => 'datetime',
        'costo_pieza' => 'decimal:2',
        'costo_mano_obra' => 'decimal:2',
    ];

    public function cita(): BelongsTo { return $this->belongsTo(Cita::class); }
    public function mecanico(): BelongsTo { return $this->belongsTo(Mecanico::class); }
}
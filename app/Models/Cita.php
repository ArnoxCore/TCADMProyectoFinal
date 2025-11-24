<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Cita extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cliente_id','vehiculo_id','mecanico_id','fecha','hora_inicio','hora_fin',
        'estatus','observaciones_cliente','precio_final'
    ];

    protected $casts = [
        'fecha' => 'date',
        'check_in_at' => 'datetime',
        'inicio_real_at' => 'datetime',
        'asistio' => 'boolean',
    ];

    public function cliente(): BelongsTo { return $this->belongsTo(Cliente::class); }
    public function vehiculo(): BelongsTo { return $this->belongsTo(Vehiculo::class); }
    public function mecanico(): BelongsTo { return $this->belongsTo(Mecanico::class); }

    public function servicios(): BelongsToMany {
        return $this->belongsToMany(Servicio::class, 'citas_servicios')
                    ->withPivot(['precio_unitario'])
                    ->withTimestamps();
    }

    public function getEstatusCssAttribute()
    {
        return match ($this->estatus) {
            'pendiente'   => 'warning',
            'confirmada'  => 'info',
            'en_proceso'  => 'primary',
            'completada'  => 'success',
            'cancelada'   => 'danger',
            default       => 'secondary',
        };
    }

    public function getEstatusTextoAttribute()
    {
        return match ($this->estatus) {
            'pendiente'   => 'Pendiente de Confirmación',
            'confirmada'  => 'Confirmada',
            'en_proceso'  => 'En Proceso',
            'completada'  => 'Completada',
            'cancelada'   => 'Cancelada',
            default       => 'Desconocido',
        };
    }

    public function observaciones(): HasMany {
        return $this->hasMany(ObservacionServicio::class);
    }

    /**
     * Minutes difference between scheduled start and actual start.
     */
    public function minutosRetraso(): ?int
    {
        if (!$this->inicio_real_at) {
            return null;
        }

        $programada = Carbon::parse($this->fecha->format('Y-m-d') . ' ' . $this->hora_inicio);
        return $programada->diffInMinutes($this->inicio_real_at, false);
    }

    public function esPuntual(int $toleranciaMin = 10): ?bool
    {
        $retraso = $this->minutosRetraso();
        return $retraso === null ? null : $retraso <= $toleranciaMin;
    }
}

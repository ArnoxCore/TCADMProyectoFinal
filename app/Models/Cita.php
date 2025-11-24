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

    public const LOCAL_TIMEZONE = 'America/Mexico_City';

    protected $fillable = [
        'cliente_id','vehiculo_id','mecanico_id','fecha','hora_inicio','hora_fin',
        'estatus','observaciones_cliente','precio_final','check_in_at','inicio_real_at','asistio'
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

    public function formattedCheckIn(string $format = 'H:i'): ?string
    {
        return $this->formatAttendanceTime($this->check_in_at, $format);
    }

    public function formattedInicioReal(string $format = 'H:i'): ?string
    {
        return $this->formatAttendanceTime($this->inicio_real_at, $format);
    }

    public function getAttendanceLabelAttribute(): string
    {
        if ($this->asistio === true) {
            return $this->formattedCheckIn()
                ? 'Check-in: '.$this->formattedCheckIn()
                : 'Asistencia registrada';
        }

        if ($this->asistio === false) {
            return 'No asistió';
        }

        return 'Pendiente';
    }

    public function getAttendanceSecondaryAttribute(): ?string
    {
        return $this->formattedInicioReal()
            ? 'Inicio: '.$this->formattedInicioReal()
            : null;
    }

    public function canCheckIn(): bool
    {
        return $this->asistio === null && $this->estatus !== 'cancelada';
    }

    public function canStartService(): bool
    {
        return $this->asistio === true && $this->inicio_real_at === null;
    }

    public function canMarkNoShow(): bool
    {
        return $this->asistio === null && $this->estatus !== 'cancelada';
    }

    public function canCancelDesdeRecepcion(): bool
    {
        return in_array($this->estatus, ['pendiente', 'confirmada']);
    }

    protected function formatAttendanceTime(?Carbon $value, string $format = 'H:i'): ?string
    {
        return $value
            ? $value->copy()->timezone(self::LOCAL_TIMEZONE)->format($format)
            : null;
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

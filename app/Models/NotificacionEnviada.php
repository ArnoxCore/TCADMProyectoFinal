<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionEnviada extends Model {
    use HasFactory;
    protected $table = 'notificaciones_enviadas';
    protected $fillable = ['user_id','cita_id','tipo','asunto','mensaje','enviado_at'];
    protected $casts = ['enviado_at' => 'datetime'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function cita(): BelongsTo { return $this->belongsTo(Cita::class); }
}
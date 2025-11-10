<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model {
    use HasFactory;
    protected $fillable = ['user_id','direccion','rfc','fecha_nacimiento'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function vehiculos(): HasMany { return $this->hasMany(Vehiculo::class); }
    public function citas(): HasMany { return $this->hasMany(Cita::class); }
}
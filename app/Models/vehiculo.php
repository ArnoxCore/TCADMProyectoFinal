<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehiculo extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable = ['cliente_id','marca','modelo','ano','placa','vin','color','kilometraje'];

    public function cliente(): BelongsTo { return $this->belongsTo(Cliente::class); }
    public function citas(): HasMany { return $this->hasMany(Cita::class); }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleMake extends Model
{
    protected $fillable = [
        'nhtsa_id',
        'nombre',
    ];

    public function modelos(): HasMany
    {
        return $this->hasMany(VehicleModel::class, 'vehicle_make_id');
    }
}

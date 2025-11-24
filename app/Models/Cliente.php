<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'direccion',
        'rfc',
        'fecha_nacimiento',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isProfileComplete()
    {
        return $this->direccion
            && $this->rfc
            && $this->fecha_nacimiento
            && $this->user
            && $this->user->phone;
    }
}

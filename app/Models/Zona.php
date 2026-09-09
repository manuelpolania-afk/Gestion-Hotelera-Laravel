<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zona extends Model
{
    protected $table = 'zonas';

    protected $fillable = [
        'codigo_zona',
        'descripcion',
    ];

    public function habitaciones(): HasMany
    {
        return $this->hasMany(Habitacion::class);
    }
}

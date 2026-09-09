<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Habitacion extends Model
{
    protected $table = 'habitaciones';

    protected $fillable = [
        'zona_id',
        'nombre_habitacion',
        'capacidad',
        'valor',
        'estado',
    ];

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class);
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(ImagenHabitacion::class);
    }

    public function imagenPrincipal(): HasOne
    {
        return $this->hasOne(ImagenHabitacion::class)->where('principal', true);
    }

    /**
     * Scope que filtra habitaciones disponibles.
     */
    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->where('estado', 'Disponible');
    }
}

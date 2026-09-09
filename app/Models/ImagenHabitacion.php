<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImagenHabitacion extends Model
{
    protected $table = 'imagenes_habitacion';

    // La tabla no usa updated_at; solo fecha_subida como timestamp de creación
    public $timestamps = false;

    protected $fillable = [
        'habitacion_id',
        'ruta',
        'principal',
        'fecha_subida',
    ];

    protected $casts = [
        'principal'    => 'boolean',
        'fecha_subida' => 'datetime',
    ];

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
    }
}

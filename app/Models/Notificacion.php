<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'user_id',
        'reserva_id',
        'mensaje',
        'leida',
        'fecha',
    ];

    protected $casts = [
        'leida' => 'boolean',
        'fecha' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    public function scopeNoLeidas(Builder $query): Builder
    {
        return $query->where('leida', false);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Reserva extends Model
{
    protected $table = 'reservas';

    protected $fillable = [
        'habitacion_id',
        'fecha',
        'estado_reserva',
        'sub_total',
    ];

    protected $casts = [
        'fecha'     => 'date',
        'sub_total' => 'decimal:2',
    ];

    // ── Relaciones ────────────────────────────────────────────

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function detalle(): HasOne
    {
        return $this->hasOne(DetalleReserva::class);
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopeActivas(Builder $query): Builder
    {
        return $query->whereNotIn('estado_reserva', ['Cancelada']);
    }

    // ── Métodos de negocio ────────────────────────────────────

    /**
     * Finaliza todas las reservas Confirmadas cuya fecha_salida ya pasó.
     */
    public static function finalizarVencidas(): void
    {
        static::where('estado_reserva', 'Confirmada')
            ->whereHas('detalle', fn (Builder $q) => $q->where('fecha_salida', '<', today()))
            ->update(['estado_reserva' => 'Finalizada']);
    }

    /**
     * Sincroniza el estado de las habitaciones según reservas activas:
     * - Ocupada  → si tiene reserva Confirmada vigente hoy
     * - Disponible → si estaba Ocupada pero ya no tiene reserva vigente
     */
    public static function sincronizarEstadosHabitaciones(): void
    {
        $hoy = today();

        // IDs de habitaciones con reserva Confirmada vigente hoy
        $ocupadasIds = static::where('estado_reserva', 'Confirmada')
            ->whereHas('detalle', fn (Builder $q) => $q
                ->where('fecha_ingreso', '<=', $hoy)
                ->where('fecha_salida', '>', $hoy)
            )
            ->pluck('habitacion_id');

        // Marcar como Ocupada (solo si no están en Mantenimiento o Fuera de servicio)
        if ($ocupadasIds->isNotEmpty()) {
            Habitacion::whereIn('id', $ocupadasIds)
                ->whereNotIn('estado', ['Mantenimiento', 'Fuera de servicio'])
                ->update(['estado' => 'Ocupada']);
        }

        // Marcar como Disponible las que estaban Ocupadas pero ya no tienen reserva vigente
        Habitacion::where('estado', 'Ocupada')
            ->whereNotIn('id', $ocupadasIds)
            ->update(['estado' => 'Disponible']);
    }
}

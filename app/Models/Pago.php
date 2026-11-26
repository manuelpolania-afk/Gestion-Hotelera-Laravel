<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'reserva_id',
        'metodo_pago',
        'estado_pago',
        'monto',
        'referencia',
        'tarjeta_enmascarada',
        'fecha_pago',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'datetime',
    ];

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    /**
     * Simula la respuesta de una pasarela: la tarjeta terminada en 0000 siempre
     * se rechaza (caso de prueba); el resto de métodos se aprueban con un 90%
     * de probabilidad para imitar el comportamiento real sin cobrar nada.
     */
    public static function simularAprobacion(string $metodoPago, ?string $numeroTarjeta): bool
    {
        if ($metodoPago === 'Tarjeta' && str_ends_with($numeroTarjeta ?? '', '0000')) {
            return false;
        }

        return random_int(1, 100) <= 90;
    }
}

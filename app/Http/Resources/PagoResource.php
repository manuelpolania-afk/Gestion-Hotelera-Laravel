<?php

namespace App\Http\Resources;

use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Pago
 */
class PagoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reserva_id' => $this->reserva_id,
            'metodo_pago' => $this->metodo_pago,
            'estado_pago' => $this->estado_pago,
            'monto' => (float) $this->monto,
            'referencia' => $this->referencia,
            'tarjeta_enmascarada' => $this->tarjeta_enmascarada,
            'fecha_pago' => $this->fecha_pago?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
